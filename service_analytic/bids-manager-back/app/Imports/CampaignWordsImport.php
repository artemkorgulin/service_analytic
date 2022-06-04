<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Class CampaignWordsImport
 *
 * @package App\Imports
 */
class CampaignWordsImport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            'keywords'  => new CampaignKeywordsImport(),
            'stopwords' => new CampaignStopwordsImport(),
        ];
    }
}
