<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StrategyCpoExport
    implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, WithColumnWidths
{
    private array $strategyHistory;
    private int $strategyTypeId;

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
            $strategyHistory->keyword_name,
            $strategyHistory->status_name,
            $strategyHistory->shows ?: '0',
            $strategyHistory->clicks ?: '0',
            $strategyHistory->ctr ?: '0',
            $strategyHistory->avg_click_price ?: '0',
            $strategyHistory->avg_1000_shows_price ?: '0',
            $strategyHistory->cost ?: '0',
            $strategyHistory->orders ?: '0',
            $strategyHistory->profit ?: '0',
            $strategyHistory->fcpo ?: '0',
            $strategyHistory->acos ?: '0',
            $strategyHistory->kvcr ?: '0',
        ];
    }

    public function headings(): array
    {
        return [
            __('front.keyword_name'),
            __('front.status'),
            __('front.shows'),
            __('front.clicks'),
            __('front.ctr'),
            __('front.avg_1000_shows_price'),
            __('front.avg_click_price'),
            __('front.cost'),
            __('front.orders'),
            __('front.profit'),
            __('front.cpo'),
            __('front.acos'),
            __('front.kvcr'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => 'string',
            'B' => 'string',
            'C' => '#,##0',
            'D' => '#,##0',
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'I' => '#,##0',
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2
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
