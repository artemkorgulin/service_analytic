<?php

namespace App\Exports;

use App\Models\NegativeKeyword;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class NegativeKeywordsExport
 * Формирует отчёт о минус словах в excel
 * @package App\Exports
 */
class NegativeKeywordsExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize
{
    use Exportable;

    /**
     * @param NegativeKeyword $item
     * @return mixed[]
     */
    public function map($item): array
    {
        return [
            $item->id,
            $item->name,
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return NegativeKeyword::query();
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'id',
            'наименование',
        ];
    }
}
