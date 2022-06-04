<?php


namespace App\Services\V2;

use App\Models\OzProduct;
use App\Models\WebCategory;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileExistsException;

/**
 * Class FtpService
 * @package App\Services\V2
 */
class FtpService
{
    const SKU_LIST_FILE_PARSING_NAME = 'List_SKU.csv';
    const SKU_LIST_FILE_PARSING_PATH = '/hour_results_from_parsing/sku_parsing/list_sku/';
    const SKU_DATA_LIST_FILE_PARSING_PATH = '/hour_results_from_parsing/sku_parsing/date_sku';

    const TOP36_LIST_FILE_PARSING_NAME = 'List_TOP36.csv';
    const TOP36_LIST_FILE_PARSING_PATH = '/hour_results_from_parsing/top36/list_top36/';
    const TOP36_DATA_FILE_PARSING_PATH = '/hour_results_from_parsing/top36/date_top36';

    /**
     * @var string
     */
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.disks.ftp.driver');
    }

    /**
     * Создание csv с данными для парсера
     * @param $array
     * @param $file_name
     */
    public function createCsv($array, $file_name)
    {
        $file = fopen(storage_path('app/') . $file_name, "w");
        foreach ($array as $line) {
            fputcsv($file, [$line]);
        }
        fclose($file);
    }

    /**
     * Проверка подключения к серверу по ftp
     *
     * @return bool
     */
    public function validate_connection()
    {
        $host = config('filesystems.disks.ftp.host');
        $user = config('filesystems.disks.ftp.username');
        $pass = config('filesystems.disks.ftp.password');

        if (!($host && $user && $pass) && $this->disk === 'ftp') {
            return false;
        }

        try {
            Storage::disk($this->disk)->files();
            return true;
        } catch (Exception $exception) {
            report($exception);
            return false;
        }
    }

    /**
     * Создание и отправка запроса на получение данных об отслеживаемых товарах
     *
     * @throws FileNotFoundException
     */
    public function sendSkuRequestFile()
    {
        if ($this->validate_connection()) {
            $this->createCsv(OzProduct::pluck('url'), self::SKU_LIST_FILE_PARSING_NAME);
            if (Storage::disk('local')->exists(self::SKU_LIST_FILE_PARSING_NAME)) {
                $file = Storage::disk('local')->get(self::SKU_LIST_FILE_PARSING_NAME);

                Storage::disk($this->disk)->put(
                    self::SKU_LIST_FILE_PARSING_PATH . self::SKU_LIST_FILE_PARSING_NAME,
                    $file
                );
            }
        }
    }

    /**
     * Создание и отправка запроса на получение данных топ 36 товаров по категориям
     *
     * @throws FileNotFoundException
     */
    public function sendTop36RequestFile()
    {
        if ($this->validate_connection()) {
            $this->createCsv(WebCategory::pluck('external_id'), self::TOP36_LIST_FILE_PARSING_NAME);
            if (Storage::disk('local')->exists(self::TOP36_LIST_FILE_PARSING_NAME)) {
                $file = Storage::disk('local')->get(self::TOP36_LIST_FILE_PARSING_NAME);

                Storage::disk($this->disk)->put(
                    self::TOP36_LIST_FILE_PARSING_PATH . self::TOP36_LIST_FILE_PARSING_NAME,
                    $file
                );
            }
        }
    }

    /**
     * Получить список csv файлов с данными топ 36 товаров по категориям
     *
     * @return array
     */
    public function getTop36Files()
    {
        if (!$this->validate_connection()) {
            return [];
        }

        return Storage::disk($this->disk)->files(self::TOP36_DATA_FILE_PARSING_PATH);
    }

    /**
     * Получить спиcоск csv файлов с данными отслеживаемых товаров
     *
     * @return array
     */
    public function getSkuFiles()
    {
        if (!$this->validate_connection()) {
            return [];
        }

        $fileList = Storage::disk($this->disk)->files(self::SKU_DATA_LIST_FILE_PARSING_PATH);
        Log::debug("Got top36 ftp result file list:" . json_encode($fileList));

        return $fileList;
    }

    /**
     * Скачать указанный csv файл
     *
     * @return array
     */
    public function downloadCsvFile($path)
    {
        $this->validate_connection();
        if (!Storage::disk($this->disk)->exists($path)) {
            return null;
        }

        $date = [];
        if (preg_match('/.*Date_.*_(\d{4}_\d{2}_\d{2}_\d{2}_\d{2})\.csv/', $path, $date)) {
            $date = $date[1];

            if ($date) {
                $date = Carbon::createFromFormat('Y_m_d_H_i', $date)->toIso8601String();
            }
        }

        if (!$date) {
            return null;
        }

        $file_name = explode('/', $path);
        $file_name = end($file_name);
        $file = Storage::disk($this->disk)->get($path);
        Storage::disk('local')->put($file_name, $file);

        $current_dir = explode('/Date', $path)[0];
        if (!Storage::disk($this->disk)->exists($current_dir . '/proceeded')) {
            Storage::disk($this->disk)->makeDirectory($current_dir . '/proceeded');
        }

        try {
            if (Storage::disk($this->disk)->exists($current_dir . '/proceeded/' . $file_name)) {
                Storage::disk($this->disk)->delete($current_dir . '/proceeded/' . $file_name);
            }

            Storage::disk($this->disk)->move($path, $current_dir . '/proceeded/' . $file_name);
        } catch (FileExistsException $exception) {
            // File already exists, no actions needed
            report($exception);
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

        return [
            'date' => $date,
            'name' => $file_name,
        ];
    }

    /**
     * Распарсить csv файл с данными топ 36 товаров по категориям
     *
     * @param $path
     * @return array[]|null
     */
    public function parseTop36File($path)
    {
        $file_data = $this->downloadCsvFile($path);
        if (!$file_data) {
            return null;
        }

        $file_name = $file_data['name'];
        $date = $file_data['date'];

        $result = [];
        $prices = [];
        $handle = fopen(public_path('../storage/app/' . $file_name), "r");
        while (($row = fgetcsv($handle, 1000, ";")) !== false) {
            /**
             * Формат данных
             *
             * "WEB ID категории";"Цена";"Количество отзывов";"Рейтинг";"Количество фото"
             */
            $web_category_id = (int)$row[0];
            if ($web_category_id) {
                if (in_array($web_category_id, array_keys($result))) {
                    $prices[$web_category_id][] = $row[1];
                    $result[$web_category_id] = [
                        'web_category_id' => $web_category_id,
                        'max_price' => $result[$web_category_id]['max_price'] > $row[1] ? $result[$web_category_id]['max_price'] : $row[1],
                        'min_price' => $result[$web_category_id]['min_price'] < $row[1] ? $result[$web_category_id]['min_price'] : $row[1],
                        'min_reviews' => $result[$web_category_id]['min_reviews'] < $row[2] ? $result[$web_category_id]['min_reviews'] : $row[2],
                        'max_reviews' => $result[$web_category_id]['max_reviews'] > $row[2] ? $result[$web_category_id]['max_reviews'] : $row[2],
                        'min_rating' => $result[$web_category_id]['min_rating'] < $row[3] ? $result[$web_category_id]['min_rating'] : $row[3],
                        'min_photo' => $result[$web_category_id]['min_photo'] < $row[4] ? $result[$web_category_id]['min_photo'] : $row[4],
                    ];
                } else {
                    $prices[$web_category_id] = [$row[1]];
                    $result[$web_category_id] = [
                        'web_category_id' => $web_category_id,
                        'max_price' => $row[1],
                        'min_price' => $row[1],
                        'min_reviews' => $row[2],
                        'max_reviews' => $row[2],
                        'min_rating' => $row[3],
                        'min_photo' => $row[4],
                    ];
                }
            }
        }
        fclose($handle);

        Storage::disk('local')->delete($file_name);
        foreach (array_keys($result) as $web_category_id) {
            $avg = 0;
            foreach ($prices[$web_category_id] as $price) {
                $avg += (int)$price;
            }
            $result[$web_category_id]['average_price'] = $avg / count($prices[$web_category_id]);
            $result[$web_category_id]['created_at'] = $date;
        }

        return [
            $date => $result
        ];
    }

    /**
     * Распарсить csv файл с данными отслеживаемого товара
     *
     * @param $path
     * @return array|null
     */
    public function parseSkuFile($path)
    {
        Log::debug("Downloading sku result file from ftp: $path");
        $file_data = $this->downloadCsvFile($path);
        if (!$file_data) {
            return null;
        }

        $file_name = $file_data['name'];
        $date = $file_data['date'];

        $result = [];
        $local_path = public_path('../storage/app/' . $file_name);
        $handle = fopen($local_path, "r");
        Log::debug("Opening file: $local_path");

        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            /**
             * Формат данных
             *
             * "URL","WEB ID категории","Полное название категории","Название конечной категории","Рейтинг","Кол-во отзывов"
             */
            $web_category_id = (int)$row[1];
            if ($web_category_id) {
                if (in_array($web_category_id, array_keys($result))) {
                    $result[$web_category_id]['products'][] = [
                        'rating' => $row[4],
                        'reviews' => $row[5],
                        'url' => $row[0],
                    ];
                } else {
                    $result[$web_category_id] = [
                        'web_category_id' => $web_category_id,
                        'full_name' => $row[2],
                        'name' => $row[3],
                        'date' => $date,
                        'products' => [
                            [
                                'rating' => $row[4],
                                'reviews' => $row[5],
                                'url' => $row[0],
                            ]
                        ]
                    ];
                }
            }
        }
        fclose($handle);

        Storage::disk('local')->delete($file_name);

        return $result;
    }
}
