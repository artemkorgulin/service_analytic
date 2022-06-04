<?php

namespace App\Http\Requests\Ozon;

use App\Models\Feature;
use App\Models\OzCategory;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzonProductFeatureUpdateRequest extends BaseRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @param  OzCategory  $productCategory
     * @return array
     */
    public static function getFeatureRules(Feature $feature): array
    {
        $rules = [];
        // Validation depend on type of field
        if (!$feature->is_reference && $feature->type) {
            $rules = match (true) {
                $feature->type === 'String' => ['nullable', 'string', 'max:255'],
                $feature->type === 'Decimal' => ['nullable', 'numeric'],
                $feature->type === 'URL' => ['nullable', 'url'],
                $feature->type === 'Integer' => ['nullable', 'integer'],
                $feature->type === 'ImageURL' => ['nullable', 'url', 'regex:/.*\.(jpg|jpeg|gif|png)$/'],
                $feature->type === 'Boolean' => ['nullable', 'boolean'],
                default => [],
            };

            if ($feature->is_required && $rules) {
                array_push($rules, 'required');
            }
        }

        return $rules;
    }
}
