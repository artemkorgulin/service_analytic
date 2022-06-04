<?php

namespace App\Actions\Words;

use App\Actions\Action;
use App\DataTransferObjects\Frontend\Words\WordsSaveFromXlsRequestData;
use App\Tasks\Words\WordsImportXlsTask;

/**
 * Class WordsSaveFromXlsAction
 *
 * @package App\Actions\Words
 */
class WordsSaveFromXlsAction extends Action
{
    /**
     * @param WordsSaveFromXlsRequestData $requestData
     * @return array
     */
    public function run(WordsSaveFromXlsRequestData $requestData): array
    {
        $importWords = (new WordsImportXlsTask())->run($requestData->wordsXls);

        $keywords = [];
        foreach ($importWords['keywords'] as $importKeyword) {
            if (!is_null($importKeyword['klyucevoe_slovo'])) {
                $keywords[] = $importKeyword['klyucevoe_slovo'];
            }
        }
        $stopWords = [];
        foreach ($importWords['stopwords'] as $importStopWord) {
            if (!is_null($importStopWord['minus_slovo'])) {
                $stopWords[] = $importStopWord['minus_slovo'];
            }
        }

        $words = [
            'keywords' => $keywords,
            'stopwords' => $stopWords
        ];

        return (new SaveCampaignProductWordsSubAction())->run($requestData->campaignProductId, $words);
    }
}
