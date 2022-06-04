<?php

namespace App\Http\Requests\Ozon;

use App\Http\Requests\Ozon\OzonProductFeatureUpdateRequest;
use App\Http\Requests\OzProductRequest;
use App\Models\OzProduct;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Validation\ValidationException;

class OzonProductMassUpdateRequest extends BaseRequest
{
    public string $prefix = 'items.*.';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->validate([
            'items' => ['array', 'required', 'min:1'],
            $this->prefix . 'id' => ['integer', 'required', 'exists:App\Models\OzProduct,id'],
        ]);

        $featureRules = $this->getFeaturesRulesArray();
        $prices = $this->pricesValidation();

        $other = [
            $this->prefix . 'name' => ['string', 'min:2', 'max:255'],
            $this->prefix . 'characteristics' => ['array'],
            $this->prefix . 'characteristics.*.id' => ['required', 'integer'],
            $this->prefix . 'images' => ['array', 'nullable'],
            $this->prefix . 'images.*' => ['url', 'regex:/.*\.(jpg|jpeg|gif|png)$/'],
            $this->prefix . 'images360' => ['array', 'nullable'],
            $this->prefix . 'images360.*' => ['url', 'regex:/.*\.(jpg|jpeg|gif|png)$/'],
            $this->prefix . 'colorSample' => ['url', 'nullable', 'regex:/.*\.(jpg|jpeg|gif|png)$/'],
            $this->prefix . 'characteristics.*.id' => ['numeric'],
            $this->prefix . 'characteristics.*.value' => ['string'],
            $this->prefix . 'characteristics.*.selected_options' => ['array'],
            $this->prefix . 'characteristics.*.selected_options.*' => ['numeric', 'min:1'],
        ];

        return array_merge($other, $prices, $featureRules);
    }

    /**
     * @return array
     */
    public function getFeaturesRulesArray()
    {
        $items = $this->input('items');

        $product = OzProduct::query()
            ->where('id', $items[array_key_first($items)]['id'])
            ->first();

        if (!$product->exists()) {
            throw new ValidationException('Empty product selected.');
        }

        if (!$product->category->exists()) {
            throw new ValidationException('Empty category id in product id - ' . $product->id . '.');
        }

        $features = $product->category->features;

        $featureRules = [];
        $featurePrefix = $this->prefix . 'characteristics.';

        foreach ($features as $feature) {
            $featureRules[$featurePrefix . $feature->id . '.value'] = OzonProductFeatureUpdateRequest::getFeatureRules($feature);
        }

        return $featureRules;
    }

    /**
     * Prices validation
     */
    private function pricesValidation()
    {
        $priceProductsRules = [];

        foreach ($this->input('items') as $keyProduct => $product) {
            $priceProductRule = OzProductRequest::pricesValidation(
                $product['price'],
                $product['old_price'] ?? 0,
                $product['premium_price'] ?? 0
            );

            foreach ($priceProductRule as $ruleKey => $itemPrice) {
                $priceProductsRules['items.' . $keyProduct . '.' . $ruleKey] = $itemPrice;
            }
        }

        return $priceProductsRules;
    }

}
