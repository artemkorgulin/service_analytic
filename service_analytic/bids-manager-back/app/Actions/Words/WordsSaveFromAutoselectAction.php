<?php

namespace App\Actions\Words;

use App\Actions\Action;
use App\DataTransferObjects\Frontend\Words\WordsSaveFromAutoselectRequestData;

/**
 * Class WordsSaveFromAutoselectAction
 */
class WordsSaveFromAutoselectAction extends Action
{
    public function run(WordsSaveFromAutoselectRequestData $requestData)
    {
        $words = array_merge(['keywords' => $requestData->keywords], ['stopwords' => $requestData->stopwords]);
        $res = (new SaveCampaignProductWordsSubAction())->run($requestData->campaignProductId, $words);
        return $res;
    }
}
