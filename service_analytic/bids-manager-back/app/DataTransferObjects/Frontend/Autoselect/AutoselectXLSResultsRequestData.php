<?php

namespace App\DataTransferObjects\Frontend\Autoselect;

use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Http\Request;

class AutoselectXLSResultsRequestData extends DataTransferObject
{
    /** @var int $autoselectParameterId */
    public int $autoselectParameterId;

    /**
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request): self
    {
        return new static([
            'autoselectParameterId' => (int)$request->input('autoselect_parameter_id'),
        ]);
    }
}
