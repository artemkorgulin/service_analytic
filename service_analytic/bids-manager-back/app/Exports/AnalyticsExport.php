<?php

namespace App\Exports;

use App\Contracts\StatisticInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsExport extends StatisticExport
    implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize, WithColumnWidths
{
    private array $campaigns = [];

    /**
     * CampaignExport constructor.
     *
     * @param array $campaigns
     */
    public function __construct($campaigns = [])
    {
        $this->campaigns = $campaigns;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect($this->campaigns);
    }

    /**
     * @param StatisticInterface $campaign
     * @return array
     */
    public function map($campaign): array
    {
        return [
            isset($campaign->date) ? Date::dateTimeToExcel(Carbon::parse($campaign->date)) : 'Итого:',
            $campaign->popularity ?: '0',
            $campaign->shows ?: '0',
            $campaign->purchased_shows ?: '0',
            $campaign->clicks ?: '0',
            $campaign->ctr ?: '0',
            $campaign->avg_1000_shows_price ?: '0',
            $campaign->avg_click_price ?: '0',
            $campaign->cost ?: '0',
            $campaign->orders ?: '0',
            $campaign->profit ?: '0',
            $campaign->cpo ?: '0',
            $campaign->acos ?: '0',
            $campaign->drr ?: '0'
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            __('front.date'),
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
            __('front.drr'),
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'B' => '#,##0',
            'C' => '#,##0',
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'E' => '#,##0',
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'J' => '#,##0',
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 15,
        ];
    }
}
