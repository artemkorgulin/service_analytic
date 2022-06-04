<?php

namespace App\Console\Commands;

use App\Services\V2\WebCategoryServiceUpdater;
use Exception;
use Illuminate\Console\Command;

/**
 * Class V2ParseSkuFtp
 * Позволяет выгрузить с ftp результаты парсинга карточек товаров
 * @package App\Console\Commands
 */
class V2ParseSkuFtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ftp:parse_sku';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для получения результата парсинга карточек товаров';

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
        $this->info('Получение результата парсинга карточек товаров');
        try {
            (new WebCategoryServiceUpdater())->updateSku();
            $this->info('Файлы для парсинга успешно получены и обработаны');
            return 0;
        } catch (Exception $exception) {
            report($exception);
            $this->error('Произошла ошибка при получении/обработке: ' . $exception->getMessage());
            return 1;
        }
    }
}
