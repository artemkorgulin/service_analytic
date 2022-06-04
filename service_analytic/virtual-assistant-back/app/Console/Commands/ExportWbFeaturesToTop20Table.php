<?php

namespace App\Console\Commands;

use App\Models\WbDirectory;
use App\Models\WbDirectoryItem;
use App\Models\WbTop20Features;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExportWbFeaturesToTop20Table extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:export-features-to-top20-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Экспортируем данные из таблиц характеристик товаров Wildberries в таблицу top20 характеристик';


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
        $this->alert('Приступаю к обработки данных из основных словарей');
        $this->_handle_main_dictionaries();

        $this->alert('Приступаю к обработки данных из словаря tnved (ТНВЭД)');
        $this->_handle_tnved_dictionary();

        $this->alert('Приступаю к обработки данных из словаря ext (доп характеристики)');
        $this->_handle_ext_dictionary();

        return 0;
    }

    /**
     * Обработка характеристик и вытаскивание топ 20 значений для словарей
     * за исключением ext и tnved
     */
    private function _handle_main_dictionaries() {
        foreach (WbDirectory::select()->whereNotIn('slug', ['/tnved', '/ext'])->get() as $directory) {
            $this->info("Получаем top-20 значений для словаря '{$directory->title}'");

            $items = $directory->items()->where('wb_directory_items.title', 'regexp', "^[a-zA-Zа-яА-Я0-9]+")
                    ->whereRaw('LENGTH(wb_directory_items.title) > 1')
                    ->orderBy('wb_directory_items.popularity', 'DESC')
                    ->orderBy('wb_directory_items.title', 'ASC')
                    ->select('wb_directory_items.title AS title', 'wb_directory_items.popularity AS popularity', 'has_in_ozon as has_in_ozon')
                    ->take(20)
                    ->get();

            if ($items) {
                WbTop20Features::where(['directory_slug' => $directory->slug, 'directory_id' => $directory->id])
                    ->delete();
                foreach ($items as $item) {
                    (new WbTop20Features([
                        'directory_slug' => $directory->slug,
                        'directory_id' => $directory->id,
                        'title' => $item->title,
                        'popularity' => $item->popularity,
                        'has_in_ozon' => $item->has_in_ozon,
                    ]))->save();
                }
            }
        }

    }

    /**
     * Обработка значений словаря tnved и вставка значений в таблицу top 20
     */
    private function _handle_tnved_dictionary() {

    }

    /**
     * Обработка значения словаря ext и вставка в таблицу top 20
     */
    private function _handle_ext_dictionary() {

    }
}
