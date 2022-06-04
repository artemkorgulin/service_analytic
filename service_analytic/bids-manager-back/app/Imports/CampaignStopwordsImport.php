<?php

namespace App\Imports;


use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Class CampaignStopwordsImport
 *
 * @package App\Imports
 */
class CampaignStopwordsImport implements ToArray, WithHeadingRow
{
    public function array(array $rows)
    {
        foreach ($rows as $row) {
            $a = $row;
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
