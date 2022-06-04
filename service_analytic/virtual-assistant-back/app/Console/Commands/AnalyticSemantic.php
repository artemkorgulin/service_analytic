<?php

namespace App\Console\Commands;

use App\Models\PlatformSemanticsRufago;
use App\Exceptions\Wb\WbApiException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnalyticSemantic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytic:semantic {--filename= : Имя файла для парсинга }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сопоставление категорий';
    private mixed $connection;

    const PATH_FROM_PARSING = '/root_queries_results_from_parsing_wb/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = env('FTP_VA_CONNECTION');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('filename')) {
            $fileName = self::PATH_FROM_PARSING.trim($this->option('filename'));
        } else {
            $fileName = $this->checkNewFile();
        }

        if (!$fileName) {
            return Command::FAILURE;
        }

        $timeStart = microtime(true);
        $fails = [];

        $this->info('Формирование семантического ядра');
        $this->info('Файл: '.$fileName);
        $isFirst = true;


        foreach (self::getRows($fileName) as $row) {
            try {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                if (!$row) {
                    continue;
                }

                $row = explode(';', $row);

                if ($row[1] == 0 || $row[1] == "") {
                    throw new WbApiException(0);
                }

                if ($row[11] == 0 || $row[11] == "") {
                    throw new WbApiException(1);
                }

                $search_response = trim($row[11], '"');
                $wb_product_name = trim($row[1], '"');

                if ($search_response == 0 || $search_response == "") {
                    throw new WbApiException(1);
                }


                if ($wb_product_name == 0 || $wb_product_name == "") {
                    throw new WbApiException(0);
                }

                PlatformSemanticsRufago::query()->updateOrCreate(
                    [
                        'wb_product_name' => $wb_product_name,
                        'wb_parent_name' => trim($row[3], '"'),
                        'oz_category_name' => $row[9] ? trim($row[9], '"') : '',
                        'key_request' => trim($row[6], '"'),
                        'search_response' => $search_response,
                    ],
                    [
                        'wb_product_id' => (int) trim($row[0], '"'),
                        'wb_parent_id' => (int) trim($row[2], '"'),
                        'oz_category_id' => (int) trim($row[8], '"'),
                        'popularity' => (int) trim($row[12], '"')
                    ]
                );
            } catch (Exception $exception) {
                report($exception);
                $fails[] = $row;
                $this->error($exception->getMessage());
            }
        }

        $this->info(count($fails));
        $this->info(print_r($fails, true));

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $this->info('Формирование семантического ядра закончено '.$time);

        $this->info('Удалить бренды из характеристик');

        $stream = Storage::disk($this->connection)->readStream('/ozon/brand.csv');

        $bar = $this->output->createProgressBar(1000);
        $this->info(date('Y-m-d H:i:s'));
        $bar->start();

        while (($data = fgetcsv($stream)) !== false) {
            self::clearKeyRequest($data[0]);
            $bar->advance();
        }

        $this->info("");
        $this->info(date('Y-m-d H:i:s'));
        $bar->finish();
        $this->info('Удаление  брендов из характеристик успешно завершено');
        return Command::SUCCESS;
    }

    /**
     * @param  string  $file
     * @return \Generator
     * @throws Exception
     */
    public function getRows(string $file): \Generator
    {
        $handle = Storage::disk('ftp')->readStream($file);
        if (!$handle) {
            throw new Exception();
        }
        while (!feof($handle)) {
            yield fgets($handle);
        }
        fclose($handle);
    }

    public function clearKeyRequest(string $brand): void
    {
        $platformSemantics = PlatformSemanticsRufago::query()
            ->select('id', 'key_request')
            ->where('search_response', 'LIKE', '%'.$brand.'%')
            ->cursor();

        foreach ($platformSemantics as $key => $platformSemantic) {
            if (!Str::contains($platformSemantic->key_request, $brand)) {
                continue;
            }
            $platformSemantic->key_request = Str::of($platformSemantic->key_request)->replace($brand, '');
            $platformSemantic->save();
        }
    }

    /**
     * @return string|null
     */
    private function checkNewFile(): ?string
    {
        $files = Storage::disk('ftp')->files(self::PATH_FROM_PARSING);
        $result = [];
        foreach ($files as $file) {
            $result[Storage::disk('ftp')->lastModified($file)] = $file;
        }

        return end($result);
    }
}
