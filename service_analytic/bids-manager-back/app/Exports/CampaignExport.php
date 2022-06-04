<?php

namespace App\Exports;

use App\Models\CampaignStatistic;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CampaignExport extends StatisticExport
    implements FromCollection,
               WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize, WithColumnWidths,
               WithCalculatedFormulas, WithEvents
{
    private array $campaigns = [];

    /**
     * CampaignExport constructor.
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
     * @param CampaignStatistic $campaign
     * @return array
     */
    public function map($campaign): array
    {
        $budget = isset($campaign->daily_budget)
            ? ($campaign->daily_budget > 0 ? $campaign->daily_budget : __('front.budget_not_set'))
            : '';

        $startDate = isset($campaign->campaign_start_date)
            ? ( $campaign->campaign_start_date != '0000-00-00' && $campaign->campaign_start_date != '00.00.0000'
                ? Date::dateTimeToExcel(Carbon::parse($campaign->campaign_start_date))
                : '' )
            : '';
        $endDate = isset($campaign->campaign_end_date)
            ? ( $campaign->campaign_end_date != '0000-00-00' && $campaign->campaign_end_date != '00.00.0000'
                ? Date::dateTimeToExcel(Carbon::parse($campaign->campaign_end_date))
                : '' )
            : '';

        return [
            $campaign->campaign_id ?? __('front.totals'),
            $campaign->campaign_name ?? '',
            $campaign->campaign_page_type_name ?? '',
            $campaign->campaign_status_name ?? '',
            $budget,
            $campaign->strategy_name ?? '',
            $startDate,
            $endDate,
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
            $campaign->drr ?: '0',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            __('front.campaign_id'),
            __('front.campaign_name'),
            __('front.page_type'),
            __('front.campaign_status'),
            __('front.daily_budget'),
            __('front.strategy_name'),
            __('front.start_date'),
            __('front.end_date'),
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
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'L' => '#,##0',
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'Q' => '#,##0',
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'T' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'U' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 11,
            'B' => 33,
            'C' => 14,
            'D' => 14,
            'E' => 14,
            'F' => 30,
            'G' => 14,
            'H' => 14,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 15,
            'O' => 15,
            'P' => 15,
            'Q' => 15,
            'R' => 15,
            'S' => 15,
            'T' => 15,
            'U' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->getSheet();
                $lastRow = $sheet->getHighestDataRow();
                for( $i = 2; $i <= $lastRow - 1; $i++ ) {
                    //$sheet->setCellValue('H'.$i, '=ЕСЛИ(F'.$i.'>0;G'.$i.'/F'.$i.';0)');
                }
            }
        ];
    }
}
