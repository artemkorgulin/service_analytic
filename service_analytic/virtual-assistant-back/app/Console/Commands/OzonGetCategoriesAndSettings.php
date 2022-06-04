<?php

namespace App\Console\Commands;

use App\Models\OzCategory;
use App\Services\V2\OzonApi;
use Illuminate\Console\Command;

class OzonGetCategoriesAndSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:get-categories-and-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение и обновление информации по всем товарным группам в Ozon и получение полей
    по умолчанию для групп';

    protected $chunkSize = 20;

    private $ozonApiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->ozonApiService = new OzonApi(config('env.ozon_command_client_id'),
            config('env.ozon_command_api_key'));

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
