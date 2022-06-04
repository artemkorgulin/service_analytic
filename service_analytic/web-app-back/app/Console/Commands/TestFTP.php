<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use App\Services\FtpService;
use Carbon\Carbon;

//use Faker\Generator as Faker;


class TestFTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ftp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        if (App::environment('local')) {
            $directory = '/local';
        } elseif (App::environment('test')) {
            $directory = '/test';
        } else {
            $directory = '/';
        }

        /**
         * Для 1С складываем в директорию input/(card, invoice)
         *
         * Читаем ответ из 1С из output/(card, invoice)
         */

//        (new FtpService())->copyDirectory('/output/', '/archive/');
//        (new FtpService())->clearAllDirectory('/input/', '/archive/');

        $date = new \DateTime(now());
        $date = $date->format('Y-m-d');

//        $files = Storage::disk('ftp')->makeDirectory('/local/output/invoice');
//        $files = Storage::disk('ftp')->makeDirectory('/local/input/invoice');
//        //$files = Storage::disk('ftp')->makeDirectory('/input/notify');

//        $files = Storage::disk('ftp')->deleteDirectory('/input/card');
//        $content = json_encode($objData, JSON_UNESCAPED_UNICODE,);
//        Storage::disk('ftp')->put('/output/invoice/' . $objData->invoice . '_'. $date .'.json', $content, 'public');

        $files = Storage::disk('ftp')->files('/archive/output/card/');
        $this->info('Спмсок файлов в директории  /archive/output/card/');
        dump($files);
        self::getDataFiles($files);

        $files = Storage::disk('ftp')->files('/archive/output/invoice/');
        $this->info('Спмсок файлов в директории  /archive/output/invoice/');
        dump($files);
        self::getDataFiles($files);

        $directoryes = Storage::disk('ftp')->allDirectories('/');
        dump($directoryes, 123);
        $files = Storage::disk('ftp')->directories('/');
        dump('/', $files);
        $files = Storage::disk('ftp')->directories('/output/');
        dump('/output/', $files);
        $files = Storage::disk('ftp')->directories('/input/');
        dump('/input/', $files);
        $files = Storage::disk('ftp')->directories('/archive/');
        dump('/archive/', $files);

        $files = Storage::disk('ftp')->files('/input/invoice/');
        $this->info('Спмсок файлов в директории  /input/invoice/');
        dump($files);
        self::getDataFiles($files);
        $files = Storage::disk('ftp')->files('/archive/');
        $this->info('Спмсок файлов в директории  /archive/');
        dump($files);
        self::getDataFiles($files);


        $files = Storage::disk('ftp')->files('/input/card/');
        $this->info('Спмсок файлов в директории  /input/card/');
        dump($files);
        self::getDataFiles($files);

        $files = Storage::disk('ftp')->files('/output/invoice/');
        $this->info('Спмсок файлов в директории  /output/invoice/');
        dump($files);
        self::getDataFiles($files);

        $files = Storage::disk('ftp')->files('/output/card/');
        $this->info('Спмсок файлов в директории  /output/card/');
        dump($files);
        self::getDataFiles($files);

        $files = Storage::disk('ftp')->files('/local/input/invoice/');
        $this->info('Спмсок файлов в директории  /local/input/invoice/');
        dump($files);

    }

    public function getDataFiles($files)
    {
        foreach ($files as $file) {
            $this->info('Файл: '.$file);
            $this->info('Содержимое файла '.$file);
            $content = Storage::disk('ftp')->get($file);
            dump($content);
        }
    }
}
