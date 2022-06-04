<?php

namespace App\Http\Requests\Download;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\Validator;

/**
 * @property $id
 */
class DownloadAbuseRequest extends BaseRequest
{
    protected static $RULES = [
        'id' => 'required|integer|exists:App\Models\EscrowAbuse,id'
    ];

    public function validationData(): array
    {
        $pathParts = explode('/', $this->path());
        $id = $pathParts[2] ?? null;
        return array_merge($this->all(), ['id' => $id]);
    }

    /**
     * Throw nothing to web-app-back (it will throw 404)
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator) : void
    {
    }
}
