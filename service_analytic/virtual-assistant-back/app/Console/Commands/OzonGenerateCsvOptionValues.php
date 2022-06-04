<?php

namespace App\Console\Commands;

use App\Models\Feature;
use App\Models\OzOptionFrequency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OzonGenerateCsvOptionValues extends Command
{

    /**
     * Сколько записей из БД обрабатываем за один проход
     * @var int
     */
    protected $chunkSize = 50;
    protected $chunkSize2 = 10000;

    /**
     * Название файла CSV
     * @var string
     */
    protected $CSVFileName = 'option_values.csv';
    protected $CSVFileName2 = 'option_values2.csv';

    // Итератор
    protected $i = 1;


    protected $findInsert = ['назначения', 'тип', 'особенност'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:generate-csv-option-values';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерируем CSV файл для парсинга со значениями характеристик Ozon';

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
        $filepath = storage_path($this->CSVFileName);
        $this->alert('Создаю CSV файл в папке ' . $filepath);
        $fp = fopen($filepath, 'w+');
        fputcsv($fp, ['Значение характеристики', 'Сколько раз используется']);

        // Получаем все НЕОБХОДИМЫЕ характеристики
        $features = Feature::select('id')->where(function ($query) {
            foreach ($this->findInsert as $f) {
                $query->orWhere('name', 'LIKE', '%' . $f . '%');
            }
        })
            ->where('is_collection', 1)
            ->where('count_values', '<', 400)
            ->groupBy('id')
            ->pluck('id')
            ->toArray();

        DB::statement("TRUNCATE TABLE oz_feature_option_tmp;");

        $start = date('Y-m-d h:i:s', time());

        $this->info($start);

        DB::statement("INSERT INTO oz_feature_option_tmp (feature_id, option_id) SELECT oz_feature_to_option.feature_id, oz_feature_to_option.option_id FROM oz_feature_to_option WHERE (oz_feature_to_option.feature_id IN (".
            implode(',', $features).")) GROUP BY oz_feature_to_option.feature_id, oz_feature_to_option.option_id ORDER BY option_id");

        $stop = date('Y-m-d h:i:s', time());

        $this->info($stop);


        // Получаем ТОЛЬКО id необходимых значений
        DB::table('oz_feature_option_tmp')
            ->select('option_id')
            ->groupBy('option_id')
            ->orderBy('option_id')
            ->chunk($this->chunkSize2, function ($collection) use ($fp) {

                $optionIds = [];
                $collection->map(function ($item) use (&$optionIds) {
                    $optionIds[] = $item->option_id;
                });
                $this->info("Выполняем итерацию {$this->i} по {$this->chunkSize2} значений характеристик");
                $this->i++;

                $options = [];

                DB::table('oz_feature_options')
                    ->select('value AS option_value', DB::raw('COUNT(value) AS option_value_count'))
                    ->whereIn('id', $optionIds)
                    ->groupBy('option_value')
                    ->orderBy('option_value_count', 'DESC')
                    ->get()->map(function ($item) use (&$options) {
                        $options[] = (array)$item;
                    });

                try {
                    OzOptionFrequency::upsert($options, ['option_value'], ['option_value_count']);
                } catch (\Exception $exception) {
                    report($exception);
                    $this->error($exception->getMessage());
                }

                foreach ($options as $option) {
                    fputcsv($fp, array_values($option));
                }

            });


        fclose($fp);
    }
}
