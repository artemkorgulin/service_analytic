<?php

namespace App\Exports;

use App\Models\RootQuery;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Class RootQueriesExport
 * Формирует отчёт о корневых запросах в excel
 * @package App\Exports
 */
class RootQueriesExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize
{
    use Exportable;

    /**
     * @var RootQuery $rootQuery
     *
     * @return mixed[]
     */
    public function map($rootQuery): array
    {
        return [
            $rootQuery->id,
            $rootQuery->name,
            $rootQuery->ozonCategory->name,
            $rootQuery->aliases->pluck('name')->join(';'),
            $rootQuery->searchQueries->pluck('id')->join(';'),
            Date::dateTimeToExcel($rootQuery->updated_at),
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return RootQuery::query();
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'id запроса',
            'наименование запроса',
            'категория',
            'синонимы',
            'связанные поисковые запросы',
            'дата последнего обновления записи',
        ];
    }
}
