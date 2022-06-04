<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class ProductCharacteristicsExport
 * Формирует отчёт о характеристиках товаров в excel
 * @package App\Exports
 */
class ProductCharacteristicsExport implements FromCollection, WithStyles
{
    use Exportable;

    const TYPE_FILLED = 'filled';
    const TYPE_ALL = 'all';

    protected $boldCells = [];

    protected $product;
    protected $type;

    /**
     * ProductCharacteristicsExport constructor.
     * @param $product
     * @param $type
     */
    function __construct($product, $type)
    {
        $this->product = $product;
        $this->type = $type;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $res = collect([
            ['Наименование товара:', $this->product->name],
            ['SCU:', $this->product->sku],
            ['Категория', $this->product->category->name],
            ['Категория на сайте', $this->product->webCategory->name ?? ''],
            ['Цена', $this->product->price],
            ['Заполненные характеристики:'],
        ]);
        $this->boldCells[] = 'A' . $res->count();
        $characteristics = $this->product->category->characteristics;
        $filledCharacteristics = $this->product->characteristics;
        $filledCharacteristicIds = array_column($filledCharacteristics, 'id');

        $unfilledCharacteristics = array_values(array_filter($characteristics,
            function ($characteristic) use ($filledCharacteristicIds) {
                return !in_array($characteristic['id'], $filledCharacteristicIds);
            }));

        foreach ($filledCharacteristics as $filledCharacteristic) {
            if ($filledCharacteristic['is_reference']) {
                $value = implode(', ', array_map(function ($item) {
                    return $item['value'];
                }, $filledCharacteristic['options']));
            } else {
                $value = $filledCharacteristic['value'];
            }
            $res = $res->concat(collect([
                [$filledCharacteristic['name'], $value]
            ]));
        }

        if ($this->type !== self::TYPE_FILLED) {
            $res = $res->concat(collect([
                ['Незаполненные характеристики:'],
            ]));
            $this->boldCells[] = 'A' . $res->count();

            for ($i = 0; $i < count($unfilledCharacteristics); $i++) {
                $res = $res->concat(collect([
                    [
                        $unfilledCharacteristics[$i]['name'],
                        $unfilledCharacteristics[$i++ + 1]['name'] ?? ''
                    ]
                ]));
            }
        }
        return $res;
    }

    /**
     *
     * @param Worksheet $sheet
     * @return \bool[][][]
     */
    public function styles(Worksheet $sheet): array
    {
        // Добавляем жирный шрифт
        $res = [];
        foreach ($this->boldCells as $cell) {
            $res[$cell] = ['font' => ['bold' => true]];
        }
        return $res;
    }
}
