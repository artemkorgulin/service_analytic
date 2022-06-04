<?php

namespace App\Console\Commands;

use App\Models\Option;
use App\Models\WbDirectoryItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WbCleanDirectories extends Command
{

    const WB_EXT_DICT_ID = 7;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:clean-directories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка справочников Wildberries от "косячных" значений';

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
        $i = 1;
        $chunkSize = 10000;
        $this->alert("Удаляю неправильные значения в справочниках Wildberries");
        // title NOT REGEXP '^[a-zA-Zа-яА-Я0-9ёЁ]+' OR (title NOT REGEXP '^[^,^\;]+$' AND title NOT REGEXP '^[0-9,]*$')
        WbDirectoryItem::where('title', 'NOT REGEXP', '^[a-zA-Zа-яА-Я0-9ёЁ]+')->orWhere(function ($query) {
            return $query->where('title', 'NOT REGEXP', '^[^,^\;]+$')->where('title', 'NOT REGEXP', '^[0-9,]*$');
        })->chunk($chunkSize, function ($items) use ($i, $chunkSize) {
            $this->info('Очищено уже '. $chunkSize * $i . ' записей в справочнике');
            $items->delete();
        });
        $c = WbDirectoryItem::onlyTrashed()->count();
        $this->info('Всего числится удаленными ' . $c . ' записей');
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handleOld()
    {
        $i = 1;
        $this->alert("Удаляю все значения из словаря Ext");
        DB::table('wb_directory_items')->where('wb_directory_id', self::WB_EXT_DICT_ID)->delete();
        $this->alert("Начинаю обработку значений их справочников Wildberries");
        Option::where('popularity', '>', 0)->chunk(500, function ($items) use (&$i) {
            $this->info("Шаг: {$i}");
            $i++;
            foreach ($items as $item) {
                WbDirectoryItem::updateOrCreate(
                    [
                        'wb_directory_id' => self::WB_EXT_DICT_ID,
                        'title' => $item->value,
                    ],
                    [
                        'translation' => $item->value,
                        'popularity' => $item->popularity,
                        'has_in_ozon' => true,
                    ]);
            }
        });

    }
}
