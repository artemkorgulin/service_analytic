<?php

namespace App\Console\Commands;

use App\Models\OzCategory;
use App\Models\WbCategory;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GetCharacteristicsForWildberiesAndOzon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:ozon:get-characteristics';

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
        // Получаем список характеристик по Ozon
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle('Ozon characteristics');
        $sheet->setCellValue('A1', 'Ozon');
        $sheet->setCellValue('A2', 'Категория (3й уровень)');
        $sheet->setCellValue('B2', 'Характеристика');
        $sheet->setCellValue('C2', 'is collection');
        $row = 3;
        foreach (OzCategory::whereNotNull('settings')->get() as $cat) {
            if ($cat->settings) {
                foreach ($cat->settings as $characteristic) {
                    $sheet->setCellValue('A'.($row), $cat->name);
                    $sheet->setCellValue('B'.($row), $characteristic['name'] ?? '');
                    $sheet->setCellValue('C'.($row), $characteristic['is_collection'] ?? '');
                    $row++;
                }
            }
        }

        // Получаем список характеристик по Wildberries
        $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex(1);
        $spreadsheet->getActiveSheet()->setTitle('Wildberries characteristics');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Wildberries');
        $sheet->setCellValue('A2', 'Категория (3й уровень)');
        $sheet->setCellValue('B2', 'Характеристика');
        $sheet->setCellValue('C2', 'Тип');
        $sheet->setCellValue('D2', 'is collection');
        $sheet->setCellValue('E2', 'Можно выбирать только из справочника');
        $row = 3;

        foreach (WbCategory::whereNotNull('data')->get() as $cat) {
            if (isset($cat->data->addin) && $cat->data->addin) {
                foreach ($cat->data->addin as $characteristic) {
                    $sheet->setCellValue('A'.($row), $cat->name);
                    $sheet->setCellValue('B'.($row), $characteristic->type ?? '');
                    $sheet->setCellValue('C'.($row), 'основная');
                    $sheet->setCellValue('D'.($row), isset($characteristic->dictionary));
                    $sheet->setCellValue('E'.($row), $characteristic->useOnlyDictionaryValues ?? false);
                    $row++;
                }
            }
            if (isset($cat->data->nomenclature->addin) && $cat->data->nomenclature->addin) {
                foreach ($cat->data->nomenclature->addin as $characteristic) {
                    $sheet->setCellValue('A'.($row), $cat->name);
                    $sheet->setCellValue('B'.($row), $characteristic->type ?? '');
                    $sheet->setCellValue('C'.($row), 'номенклатура');
                    $sheet->setCellValue('D'.($row), isset($characteristic->dictionary));
                    $sheet->setCellValue('E'.($row), $characteristic->useOnlyDictionaryValues ?? false);
                    $row++;
                }
            }
            if (isset($cat->data->nomenclature->variation->addin) && $cat->data->nomenclature->variation->addin) {
                foreach ($cat->data->nomenclature->variation->addin as $characteristic) {
                    $sheet->setCellValue('A'.($row), $cat->name);
                    $sheet->setCellValue('B'.($row), $characteristic->type ?? '');
                    $sheet->setCellValue('C'.($row), 'вариант');
                    $sheet->setCellValue('D'.($row), isset($characteristic->dictionary));
                    $sheet->setCellValue('E'.($row), $characteristic->useOnlyDictionaryValues ?? false);
                    $row++;
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save('characteristics.xlsx');
        return 0;
    }
}
