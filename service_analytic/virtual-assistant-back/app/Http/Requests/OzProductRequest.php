<?php

namespace App\Http\Requests;

use App\Models\Feature;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class OzProductRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $characteristics = $this->characteristicsValidation();
        $prices = self::pricesValidation(
            $this->input('price') ?? 0,
            $this->input('old_price') ?? 0,
            $this->input('premium_price') ?? 0
        );

        $other = [
            'name' => ['string', 'min:2', 'max:255'],
            'characteristics' => ['array'],
            'characteristics.*.id' => ['required', 'integer'],
            'images' => ['array', 'nullable'],
            'images.*' => ['url', 'regex:/.*\.(jpg|jpeg|gif|png)$/'],
            'images360' => ['array', 'nullable'],
            'images360.*' => ['url', 'regex:/.*\.(jpg|jpeg|gif|png)$/'],
            'colorSample' => ['url', 'nullable', 'regex:/.*\.(jpg|jpeg|gif|png)$/'],
        ];
        return array_merge($other, $prices, $characteristics);
    }

    /**
     * Characteristics validation
     */
    private function characteristicsValidation()
    {
        $validation = [];
        $characteristics = $this->input('characteristics');
        if ($characteristics) {
            foreach ($characteristics as $key => $characteristic) {
                $characteristicModel = Feature::query()->findOrFail($characteristic['id']);
                $type = $characteristicModel->type;
                // Validation depend on type of field
                if (!$characteristicModel->is_reference) {
                    switch ($type) {
                        case 'String':
                            $validation["characteristics.{$key}.value"] = ['nullable', 'string', 'max:255'];
                            break;
                        case 'Decimal':
                            $validation["characteristics.{$key}.value"] = ['nullable', 'numeric'];
                            break;
                        case 'URL':
                            $validation["characteristics.{$key}.value"] = ['nullable', 'url'];
                            break;
                        case 'Integer':
                            $validation["characteristics.{$key}.value"] = ['nullable', 'integer'];
                            break;
                        case 'ImageURL':
                            $validation["characteristics.{$key}.value"] = [
                                'nullable', 'url', 'regex:/.*\.(jpg|jpeg|gif|png)$/'
                            ];
                            break;
                    }
                }
            }
        }
        return $validation;
    }

    /**
     * Prices validation
     * @param int $price
     * @param int $oldPrice
     * @param int $premiumPrice
     * @return array
     */
    public static function pricesValidation(
        int $price,
        int $oldPrice,
        int $premiumPrice
    ): array
    {
        // please look at task SE-575
        $diffPremiumPriceAndPrice = ((100 - 66) / 100) * $price;
        $validation = [
            'price' => ['required', 'numeric', 'min:1', 'max:3999999'],
            'old_price' => ['numeric', 'min:0', 'max:3999999'],
            'premium_price' => ['numeric', 'min:0', 'max:3999999'],
        ];

        if ($oldPrice > 0) {
            $diffOldPriceAndPrice = ((100 - 90) / 100) * $oldPrice;
            if ($diffOldPriceAndPrice > $oldPrice) {
                $validation['price'] = array_merge($validation['price'], ["between:$oldPrice,$diffOldPriceAndPrice"]);
            } else {
                $validation['price'] = array_merge($validation['price'], ["between:$diffOldPriceAndPrice,$oldPrice"]);
            }
        }

        if ($premiumPrice > 0) {
            $validation['premium_price'] = array_merge($validation['premium_price'], [
                'lt:price',
                "between:$diffPremiumPriceAndPrice,$price"
            ]);

            if ($price < 400) {
                $checkIfLT400 = $price - 20;
                $validation['premium_price'] = array_merge($validation['premium_price'], ["lt:$checkIfLT400"]);
            } elseif ($price >= 400 && (int)$price <= 10000) {
                $checkIf400To10000 = ((100 - 5) / 100) * $price;
                $validation['premium_price'] = array_merge($validation['premium_price'], ["lt:$checkIf400To10000"]);
            } elseif ($price > 10000) {
                $checkIfMoreThan10000 = $price - 500;
                $validation['premium_price'] = array_merge($validation['premium_price'], ["lt:$checkIfMoreThan10000"]);
            }
        }
        return $validation;
    }

}
