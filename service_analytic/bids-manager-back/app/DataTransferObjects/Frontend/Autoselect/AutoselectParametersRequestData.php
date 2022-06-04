<?php

namespace App\DataTransferObjects\Frontend\Autoselect;

use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Http\Request;

class AutoselectParametersRequestData extends DataTransferObject
{
    /** @var string $keyword */
    public string $keyword;

    /** @var $groupId */
    public $groupId;

    /** @var $campaignProductId */
    public $campaignProductId;

    /** @var $dateFrom */
    public $dateFrom;

    /** @var $dateTo */
    public $dateTo;

    /** @var $categoryId */
    public $categoryId;

    /**
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request): self
    {
        return new static([
            'keyword' => $request->input('keyword'),
            'group_id' => $request->input('group_id'),
            'campaign_product_id' => $request->input('campaign_product_id'),
            'date_from' => $request->input('start_date'),
            'date_to' => $request->input('end_date'),
            'category_id' => $request->input('category_id')
        ]);
    }
}
