<?php

namespace App\Console\Commands;

use App\Models\OzProductTmpTop36;
use App\Models\OzProductTop36;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class OzonGetTop36Analytic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:get-top36-analytic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение данных по аналитике ТОП-36 товаров по группам Ozon';

    protected string $cat = '/hour_results_from_parsing/top36/date_top36/proceeded';

    protected array $types = ['min', 'max', 'avg'];

    protected string $tmpTable = 'oz_product_tmp_top36s';
    protected string $table = 'oz_product_top36s';

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
        $this->info('Create temporary table');
        $this->createTmpTable();

        // Get data from ftp (last file for previous day)
        $this->info('Get data from ftp (last file for previous day) and insert data to temporary table');
        $this->exportFromFtpToModel();

        $this->info('Insert data for oz_product_top36s table');
        $this->exportFromTmpToProductTop36();

        $this->info('Truncate temporary and old data');
        $this->deleteOldData();

        return Command::SUCCESS;
    }

    /**
     * Import from ftp to model
     * @return int|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \League\Csv\Exception
     * @throws \League\Csv\InvalidArgument
     */
    private function exportFromFtpToModel()
    {
        $disk = Storage::disk('ftp');
        try {
            $files = $disk->files($this->cat);
        } catch (\Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());
            die;
        }

        $file = end($files);
        $csv = Reader::createFromStream($disk->readStream($file), 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        $records = $csv->getRecords();
        $collection = [];
        foreach ($records as $record) {
            if (!trim($record['WEB ID категории'])) {
                continue;
            }
            $collection[] =
                [
                    'filename' => $file,
                    'parsed_at' => Carbon::now()->toDateString(),
                    'web_category_id' => trim($record['WEB ID категории']),
                    'price' => trim($record['Цена']) ?: 0,
                    'review_count' => trim($record['Количество отзывов']) ?: 0,
                    'rating' => str_replace(',', '.', trim($record['Рейтинг'])) ?: 0,
                    'photo_count' => trim($record['Количество фото']) ?: 0,
                ];
        }
        foreach (array_chunk($collection, 5000) as $chunk) {
            DB::table($this->tmpTable)->insert($chunk);
        }

    }

    /**
     * Export data from temporary table aggregate to normal table oz_product_top36s
     */
    private function exportFromTmpToProductTop36()
    {
        foreach ($this->types as $type) {
            try {
                $rightNow = Carbon::now();
                $select = DB::table($this->tmpTable)->select(['filename', 'parsed_at', 'web_category_id',
                    DB::raw("'{$type}' AS type"), DB::raw("{$type}(price) AS price"),
                    DB::raw("{$type}(review_count) AS review_count"), DB::raw("{$type}(rating) AS rating"),
                    DB::raw("{$type}(photo_count) AS photo_count"),
                    DB::raw("'{$rightNow}' AS created_at"), DB::raw("'{$rightNow}' AS updated_at"),
                ])->groupBy(['parsed_at', 'web_category_id']);

                // Look at task SE-984
                if ($type === 'min') {
                    $select = $select->where('rating', '>', 0)
                        ->where('review_count', '>', 0);
                }

                DB::table($this->table)->insertUsing([
                    'filename', 'parsed_at', 'web_category_id', 'type', 'price', 'review_count', 'rating',
                    'photo_count', 'created_at', 'updated_at',
                ], $select);
            } catch (\Exception $exception) {
                report($exception);
                Log::channel('console')->error($exception->getMessage());
            }
        }
    }

    /**
     * Delete old data in tables
     */
    private function deleteOldData()
    {
        try {
            Schema::dropIfExists($this->tmpTable);
            $mounthAgo = Carbon::now()->subMonth()->format('Y-m-d');
            OzProductTop36::whereDate('parsed_at', '<', $mounthAgo)->delete();
        } catch (\Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());
        }
    }


    /**
     * Create temporary table for top36 parsing
     */
    private function createTmpTable()
    {
        Schema::dropIfExists($this->tmpTable);
        Schema::create($this->tmpTable, function (Blueprint $table) {
            $table->id();
            $table->string('filename')->index()->nullable(false)
                ->comment('Имя файла для парсинга');
            $table->date('parsed_at')->index()->nullable(false)
                ->comment('Дата для парсинга');
            $table->bigInteger('web_category_id')->index()->nullable(false)
                ->comment('WEB ID категории');
            $table->decimal('price', 10, 2)->index()->nullable()
                ->comment('Цена');
            $table->integer('review_count')->index()->nullable()
                ->comment('Количество отзывов');
            $table->decimal('rating', 5, 2)->index()->nullable()
                ->comment('Рейтинг');
            $table->integer('photo_count')->index()->nullable()
                ->comment('Количество фото');
            $table->temporary();
        });
    }
}
