<?php

namespace App\Exports;

use App\Models\AutoselectResult;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AutoselectResultsExport extends StatisticExport
    implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize, WithColumnWidths
{
    /** @var Collection $results */
    private Collection $results;

    /**
     * AutoselectResultsExport constructor.
     *
     * @param $results
     */
    public function __construct($results)
    {
        $this->results = $results;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect($this->results);
    }

    /**
     * @param AutoselectResult $result
     *
     * @return array
     */
    public function map($result): array
    {
        return [
            $result->name ?? '',
            $result->popularity ?? 0,
            $result->cart_add_count ?? 0,
            $result->avg_cost ?? 0,
            $result->crtc ?? 0,
            $result->category_popularity ?? 0,
            $result->category_cart_add_count ?? 0,
            $result->category_avg_cost ?? 0,
            $result->category_crtc ?? 0
        ];
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            __('autoselect.keyword_name'),
            __('autoselect.popularity') . __('autoselect.out_of_category'),
            __('autoselect.cart_add_count') . __('autoselect.out_of_category'),
            __('autoselect.avg_cost') . __('autoselect.out_of_category'),
            __('autoselect.crtc') . __('autoselect.out_of_category'),
            __('autoselect.popularity') . __('autoselect.in_category'),
            __('autoselect.cart_add_count') . __('autoselect.in_category'),
            __('autoselect.avg_cost') . __('autoselect.in_category'),
            __('autoselect.crtc') . __('autoselect.in_category'),
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
            'I' => '#,##0',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 60,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
        ];
    }
}
