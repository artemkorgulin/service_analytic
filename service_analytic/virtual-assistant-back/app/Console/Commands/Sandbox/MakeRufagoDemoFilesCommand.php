<?php

namespace App\Console\Commands\Sandbox;

use App\Models\OzProduct;
use Illuminate\Console\Command;
use App\Services\V2\FtpService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use App\Services\Demo\AppService;

class MakeRufagoDemoFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rufago:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command create demo files, like parsing rufago.';

    private FtpService $ftpService;
    private Filesystem $fileSystem;

    /**
     * Create a new command instance.
     *
     * @return void
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->ftpService = new FtpService();
        $this->fileSystem = new Filesystem();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (AppService::isProductionServer()) {
            $this->info(sprintf('Данная команда только для DEV среды разработки!'));

            return Command::FAILURE;
        }


        if (!is_dir(storage_path('app') . $this->ftpService::SKU_DATA_LIST_FILE_PARSING_PATH . '/')) {
            Storage::disk('local')->makeDirectory($this->ftpService::SKU_DATA_LIST_FILE_PARSING_PATH . '/');
        }

        $this->fileSystem->cleanDirectory(
            storage_path('app') . $this->ftpService::SKU_DATA_LIST_FILE_PARSING_PATH . '/proceeded'
        );

        if (!is_dir(storage_path('app') . $this->ftpService::TOP36_DATA_FILE_PARSING_PATH . '/')) {
            Storage::disk('local')->makeDirectory(
                $this->ftpService::TOP36_DATA_FILE_PARSING_PATH . '/'
            );
        }

        $this->fileSystem->cleanDirectory(
            storage_path('app') . $this->ftpService::TOP36_DATA_FILE_PARSING_PATH . '/proceeded'
        );

        $this->createSkuRufagoDemoFile();
        $this->createTOP36RufagoDemoFile();

        return Command::SUCCESS;
    }

    /**
     * @return void
     */
    protected function createSkuRufagoDemoFile()
    {
        $demoArray[] = [
            "URL",
            "WEB ID категории",
            "Полное название категории на сайте",
            "Название конечной категории на сайте",
            "Рейтинг",
            "Отзывов, шт"
        ];

        $fullData = array_merge($demoArray, $this->getSkuRufagoDemoArray());

        $filePath = $this->ftpService::SKU_DATA_LIST_FILE_PARSING_PATH . '/Date_SKU_' . date('Y_m_d_H_i') . '.csv';
        $this->createDemoCsv($fullData, $filePath);
    }

    /**
     * @return array
     */
    protected function getSkuRufagoDemoArray()
    {
        $demoRes = [];
        $products = OzProduct::query()->with('webCategory')->whereNotNull('web_category_id')->get();

        foreach ($products as $product) {
            $demoRes[] = [
                $product->url,
                $product->web_category_id,
                $product->webCategory->full_name,
                $product->webCategory->name,
                mt_rand(0, 5) * 0.99,
                mt_rand(0, 200),
            ];
        }

        return $demoRes;
    }

    /**
     * @return void
     */
    protected function createTOP36RufagoDemoFile()
    {
        $demoArray[] = [
            "WEB ID категории",
            "Цена",
            "Количество отзывов",
            "Рейтинг",
            "Количество фото",
        ];

        $fullData = array_merge($demoArray, $this->getTOP36RufagoDemoArray());

        $filePath = $this->ftpService::TOP36_DATA_FILE_PARSING_PATH . '/Date_TOP36_' . date('Y_m_d_H_i') . '.csv';
        $this->createDemoCsv($fullData, $filePath, ';');
    }

    /**
     * @return array
     */
    protected function getTOP36RufagoDemoArray()
    {
        $demoRes = [];
        $products = OzProduct::query()->whereNotNull('web_category_id')->get();

        foreach ($products as $product) {
            $demoRes[] = [
                $product->web_category_id,
                mt_rand(10, 1000),
                mt_rand(0, 200),
                mt_rand(0, 5) * 0.99,
                mt_rand(1, 10),
            ];
        }

        return $demoRes;
    }

    /**
     * @param array $array
     * @param string $file_name
     * @return void
     */
    protected function createDemoCsv(array $array, string $file_name, string $separator = ',')
    {
        $file = fopen(storage_path('app') . $file_name, "w");
        foreach ($array as $line) {
            fputcsv($file, $line, $separator);
        }
        fclose($file);
    }

}
