<?php

namespace App\DataTransferObjects\Frontend\Autoselect;

use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Http\Request;

class AutoselectResultsRequestData extends DataTransferObject
{
    /** @var $autoselectParameterId */
    public $autoselectParameterId;

    /** @var $filter */
    public $filter;

    /** @var $order */
    public $order;

    /**
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request): self
    {
        return new static([
            'autoselectParameterId' => $request->input('autoselect_parameter_id'),
            'filter'                => $request->input('filter'),
            'order'                 => $request->input('order'),
        ]);
    }
}
