<?php

namespace App\Tasks\Words;

use App\Imports\CampaignWordsImport;
use App\Tasks\Task;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class WordsImportXlsTask
 *
 * @package App\Tasks\Words
 */
class WordsImportXlsTask extends Task
{
    /**
     * @param $importFile
     * @return array
     */
    public function run($importFile): array
    {
        $words = Excel::toArray(new CampaignWordsImport(), $importFile);
        return $words;
    }
}
