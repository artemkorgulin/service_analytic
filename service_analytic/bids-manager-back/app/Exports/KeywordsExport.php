<?php

namespace App\Exports;

use App\Models\CampaignKeywordStatistic;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KeywordsExport extends StatisticExport
    implements FromCollection,WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize, WithColumnWidths
{
    private array $keywords = [];

    /**
     * KeywordsExport constructor.
     *
     * @param array $keywords
     */
    public function __construct($keywords = [])
    {
        $this->keywords = $keywords;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect($this->keywords);
    }

    /**
     * @param CampaignKeywordStatistic $keyword
     *
     * @return array
     */
    public function map($keyword): array
    {
        return [
            $keyword->sku ?? 'Итого:',
            $keyword->keyword_name ?? '',
            $keyword->status_name ?? '',
            (isset($keyword->keyword_name) ? ($keyword->bid ?: '0') : ''),
            $keyword->price ?? '',
            $keyword->popularity ?: '0',
            $keyword->shows ?: '0',
            $keyword->purchased_shows ?: '0',
            $keyword->clicks ?: '0',
            $keyword->ctr ?: '0',
            $keyword->avg_1000_shows_price ?: '0',
            $keyword->avg_click_price ?: '0',
            $keyword->cost ?: '0',
            $keyword->orders ?: '0',
            $keyword->profit ?: '0',
            $keyword->cpo ?: '0',
            $keyword->acos ?: '0',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            __('front.product_sku'),
            __('front.keyword_name'),
            __('front.status'),
            __('front.bid'),
            __('front.price'),
            __('front.popularity'),
            __('front.shows'),
            __('front.purchased_shows'),
            __('front.clicks'),
            __('front.ctr'),
            __('front.avg_1000_shows_price'),
            __('front.avg_click_price'),
            __('front.cost'),
            __('front.orders'),
            __('front.profit'),
            __('front.cpo'),
            __('front.acos'),
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'I' => '#,##0',
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'N' => '#,##0',
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 35,
            'C' => 14,
            'D' => 14,
            'E' => 14,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 15,
            'O' => 15,
            'P' => 15,
            'Q' => 15,
        ];
    }
}
