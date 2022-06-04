<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StrategyShowsExport
    implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, WithColumnWidths
{
    private array $strategyHistory;

    /**
     * CampaignExport constructor.
     * @param $strategyHistory
     */
    public function __construct($strategyHistory)
    {
        $this->strategyHistory = $strategyHistory;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect($this->strategyHistory);
    }

    /**
     * @param $strategyHistory
     * @return mixed[]
     */
    public function map($strategyHistory): array
    {
        return [
            Date::dateTimeToExcel(Carbon::parse($strategyHistory->date)),
            $strategyHistory->avg_popularity ?: '0',
            $strategyHistory->avg_shows ?: '0',
            $strategyHistory->avg_purchased_shows ?: '0',
            $strategyHistory->popularity ?: '0',
            $strategyHistory->shows ?: '0',
            100 * ($strategyHistory->threshold ?? 0),
            $strategyHistory->step ?? '0',
            $strategyHistory->purchased_shows ?: '0',
            $strategyHistory->clicks ?: '0',
            $strategyHistory->ctr ?: '0',
            $strategyHistory->avg_click_price ?: '0',
            $strategyHistory->avg_1000_shows_price ?: '0',
            $strategyHistory->cost ?: '0',
            $strategyHistory->orders ?: '0',
            $strategyHistory->profit ?: '0',
            $strategyHistory->cpo ?: '0',
            $strategyHistory->acos ?: '0'
        ];
    }

    public function headings(): array
    {
        return [
            __('front.date'),
            __('front.avg_popularity'),
            __('front.avg_shows'),
            __('front.avg_purchased_shows'),
            __('front.popularity'),
            __('front.shows'),
            __('front.threshold'),
            __('front.step'),
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

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'J' => '#,##0',
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'O' => '#,##0',
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
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
            'O' => 15,
            'P' => 15,
            'Q' => 15,
            'R' => 15,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['wrapText' => true]
            ],
        ];
    }
}
