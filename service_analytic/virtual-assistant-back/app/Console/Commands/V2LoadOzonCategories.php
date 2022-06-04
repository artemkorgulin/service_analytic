<?php

namespace App\Console\Commands;

use App\Services\V2\OzonApi;
use Illuminate\Console\Command;
use App\Models\OzCategory;

/**
 * Class V2LoadOzonCategories
 * Позволяет выгрузить из озона список всех категорий
 * @package App\Console\Commands
 */
class V2LoadOzonCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:load_categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузить категории Озона';

    /** @var OzonApi $ozonApiService */
    protected $ozonApiService;

    /**
     * Массив с айдишниками категорий из озона
     * @var
     */
    protected $ozonCategoriesId;

    /**
     * Переменная для chunk и
     * @var int
     */
    protected $chunkSize = 20;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->ozonApiService = new OzonApi(config('env.ozon_command_client_id'),
            config('env.ozon_command_api_key'));
    }

    /**
     * Проверка или добавление категории в БД
     *
     * @param $arCategory
     * @param int|null $parentCategory
     */
    protected function checkOrCreateCategory($arCategory, $parentCategory = null): void
    {
        /** @var OzCategory $category */
        $this->ozonCategoriesId[] = $arCategory['category_id'];
        $category = OzCategory::updateOrCreate(
            [
                'external_id' => $arCategory['category_id']
            ],
            [
                'name' => $arCategory['title'],
                'is_deleted' => 0,
                'parent_id' => $parentCategory,
            ]
        );

        foreach ($arCategory['children'] as $children) {
            $this->checkOrCreateCategory($children, $category->id);
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Обновление категорий Озона');
        $response = $this->ozonApiService->getCategoriesTree();

        if (isset($response['data'])) {
            $ozonCategories = $response['data']['result'];
            $bar = $this->output->createProgressBar(count($ozonCategories));
            $bar->start();
            foreach ($ozonCategories as $ozonCategory) {
                $this->checkOrCreateCategory($ozonCategory);
                $bar->advance();
            }
            $bar->finish();

            //теперь нужно пометить как удаленные те категории, которых больше нет в Озоне
            if ($this->ozonCategoriesId) {
                $deleted = OzCategory::query()->whereNotIn('external_id', $this->ozonCategoriesId);
                $deleted->update(['is_deleted' => 1]);
            }
            $this->info("\nОбновление категорий успешно завершено");
        } else {
            $this->error("\nПроизошла ошибка при попытке получения списка из API");
            return 1;
        }

        // Обновление поля settings
        $productTypes = OzCategory::whereNotIn('id',
            OzCategory::whereNotNull('parent_id')->pluck('parent_id')->all())->
        whereNotNull('external_id')->pluck('external_id', 'id');

        $this->info("\nНачинаем обновлять установки категорий товара\n");

        $bar = $this->output->createProgressBar(floor($productTypes->count() / $this->chunkSize));
        $bar->start();

        foreach ($productTypes->chunk($this->chunkSize) as $chunk) {
            $settingsData = $this->ozonApiService->getCategoryFeatureV3($chunk->values()->flatten()->all());
            $bar->advance();
            if ($settingsData['statusCode'] == 200) {
                $upsert = [];
                foreach ($settingsData['data']['result'] as $s) {
                    if (isset($s['attributes'])) {
                        $category = OzCategory::firstWhere('external_id', $s['category_id']);
                        $category->settings = $s['attributes'];
                        $category->save();
                    }
                }
            }
        }
        $bar->finish();
        $this->info("\nВсе готово\n");

        return 0;
    }
}
