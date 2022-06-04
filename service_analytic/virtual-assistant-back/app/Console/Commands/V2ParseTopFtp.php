<?php

namespace App\Console\Commands;

use App\Services\V2\WebCategoryServiceUpdater;
use Exception;
use Illuminate\Console\Command;

/**
 * Class V2ParseTopFtp
 * Позволяет выгрузить с ftp результаты парсинга top36 категорий товаров
 * @package App\Console\Commands
 */
class V2ParseTopFtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ftp:parse_top';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для получения результата парсинга ТОП-36 категорий';

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
        $this->info('Получение результата парсинга топа категорий');
        (new WebCategoryServiceUpdater())->updateTop36();
        $this->info('Файлы для парсинга успешно получены и обработаны');
        return 0;

    }
}
