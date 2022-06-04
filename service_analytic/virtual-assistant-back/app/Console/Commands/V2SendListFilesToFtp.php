<?php

namespace App\Console\Commands;

use App\Services\V2\FtpService;
use Exception;
use Illuminate\Console\Command;

/**
 * Class V2SendListFilesToFtp
 * Позволяет отправить на ftp информацию о всех товарах и их категориях для последующего парсинга
 * @package App\Console\Commands
 */
class V2SendListFilesToFtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ftp:send_list_files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправляет актуальные данные на ФТП';

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
        $this->info('Отправка актуальных файлов для парсинга');
        try {
            $ftp = new FtpService();
            $ftp->sendSkuRequestFile();
            $ftp->sendTop36RequestFile();
            $this->info('Файлы для парсинга успешно отправлены');
            return 0;
        } catch (Exception $exception) {
            report($exception);
            $this->error('Произошла ошибка при отправке: ' . $exception->getMessage());
            return 1;
        }
    }
}
