<?php

namespace App\DataTransferObjects\Frontend\Words;

use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Http\Request;

/**
 * Class WordsSaveFromAutoselectRequestData
 *
 * @package App\DataTransferObjects\Frontend\Words
 */
class WordsSaveFromAutoselectRequestData extends DataTransferObject
{
    /** @var int $campaignProductId */
    public int $campaignProductId;

    /** @var $keywords */
    public $keywords;

    /** @var $stopwords */
    public $stopwords;

    /**
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request): self
    {
        return new static([
            'campaignProductId' => (int)$request->input('campaign_product_id'),
            'keywords' => $request->input('keywords') ?? [],
            'stopwords' => $request->input('stopwords') ?? [],
        ]);
    }
}
