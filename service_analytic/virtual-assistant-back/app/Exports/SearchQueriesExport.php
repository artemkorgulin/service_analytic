<?php

namespace App\Exports;

use App\Models\SearchQuery;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class SearchQueriesExport
 * Формирует отчёт о поисковых запросах в excel
 * @package App\Exports
 */
class SearchQueriesExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize
{
    use Exportable;

    /**
     * @param SearchQuery $searchQuery
     * @return mixed[]
     */
    public function map($searchQuery): array
    {
        return [
            $searchQuery->id,
            $searchQuery->name,
            'да', // TODO
            $searchQuery->popularity,
            $searchQuery->additions_to_cart,
            $searchQuery->avg_price,
            $searchQuery->products_count,
            $searchQuery->rating,
            '', // rate count
            $searchQuery->rating,
            '0', // 'среднее кол-во символов в описании конкурентов'
            '0', // 'наибольшее кол-во подборок у конкурентов'
            '', // 'дата последнего обновления записи',
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return SearchQuery::query();
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'id поискового запроса',
            'наименование',
            'активность',
            'популярность',
            'кол-во добавлений в корзину',
            'средняя стоимость',
            'кол-во товаров по запросу',
            'магический коэф.',
            'кол-во отзывов',
            'рейтинг',
            'среднее кол-во символов в описании конкурентов',
            'наибольшее кол-во подборок у конкурентов',
            'дата последнего обновления записи',
        ];
    }
}
