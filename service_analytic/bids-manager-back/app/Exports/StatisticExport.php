<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatisticExport
{
    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestDataRow();
        $lastColumn = $sheet->getHighestDataColumn();

        $sheet->getStyle('A'.$lastRow.':'.$lastColumn.$lastRow)
              ->getFont();
              //->setBold(true);

        $sheet->getStyle('A1'.':'.$lastColumn.$lastRow)
              ->getAlignment()
              ->setWrapText(true);

        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
