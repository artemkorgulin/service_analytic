<?php

namespace App\DataTransferObjects\Frontend\Words;

use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Http\Request;

/**
 * Class WordsSaveFromXlsRequestData
 *
 * @package App\DataTransferObjects\Frontend\Words
 */
class WordsSaveFromXlsRequestData extends DataTransferObject
{
    /** @var int $campaignProductId */
    public int $campaignProductId;

    /** @var $wordsXls */
    public $wordsXls;

    /**
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request): self
    {
        return new static([
            'campaignProductId' => (int)$request->input('campaign_product_id'),
            'wordsXls' => $request->file('words_xls'),
        ]);
    }
}
