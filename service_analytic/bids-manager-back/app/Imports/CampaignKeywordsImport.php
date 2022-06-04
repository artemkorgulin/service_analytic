<?php

namespace App\Imports;


use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Class CampaignKeywordsImport
 *
 * @package App\Imports
 */
class CampaignKeywordsImport implements WithHeadingRow, ToArray
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
