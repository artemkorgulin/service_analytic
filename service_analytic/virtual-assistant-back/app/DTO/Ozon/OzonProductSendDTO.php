<?php

namespace App\DTO\Ozon;

use App\Models\OzCategory;
use App\Models\OzProduct;

class OzonProductSendDTO
{
    private array $requestArray = [];

    /**
     * @param  OzProduct  $ozProduct
     */
    public function __construct(private OzProduct $ozProduct)
    {
    }

    /**
     * @return array
     */
    public function fromObjectToArray(): array
    {
        $this->prepareProduct();
        $this->prepareProductFeature();

        return $this->requestArray;
    }

    /**
     * @return void
     */
    public function prepareProduct()
    {
        // @TODO Add all field to full update product

        $this->requestArray = array_merge($this->requestArray,
            [
                'id' => $this->ozProduct->external_id,
                'name' => $this->ozProduct->name,
                "offer_id" => $this->ozProduct->offer_id,
                "old_price" => (string) $this->ozProduct->old_price,
                "premium_price" => (string) $this->ozProduct->premium_price ?? 0,
                "price" => (string) $this->ozProduct->price,
                "min_ozon_price" => (string) $this->ozProduct->min_ozon_price ?? "",
                "marketing_price" => (string) $this->ozProduct->marketing_price ?? "",
                "buybox_price" => (string) $this->ozProduct->buybox_price ?? "",
                "recommended_price" => (string) $this->ozProduct->buybox_price ?? "",
                "vat" => $this->ozProduct->vat,
                "barcode" => $this->ozProduct->barcode,
                "category_id" => OzCategory::getExternalId($this->ozProduct->category_id),
                "complex_attributes" => $this->ozProduct->complex_attributes,
                "depth" => $this->ozProduct->depth,
                "dimension_unit" => $this->ozProduct->dimension_unit,
                "height" => $this->ozProduct->height,
                "width" => $this->ozProduct->width,
                "weight_unit" => $this->ozProduct->weight_unit,
                "weight" => $this->ozProduct->weight,
                "primary_image" => $this->ozProduct->primary_image,
                "image_group_id" => "",
                "images" => $this->ozProduct->images,
                "images360" => $this->ozProduct->images360,
                "pdf_list" => [],
                "color_image" => $this->ozProduct->color_image
            ]);
    }

    /**
     * @return void
     */
    public function prepareProductFeature()
    {
        $featureArray = [];
        // @TODO Complex id WTF ?
        foreach ($this->ozProduct->featuresValues->groupBy('feature_id') as $featureGroup) {
            $featuredata = [
                'complex_id' => 0,
                'id' => (int) $featureGroup->first()->feature_id,
            ];

            $values = [];

            foreach ($featureGroup as $feature) {
                $values[] = [
                    'dictionary_value_id' => (int) $feature->option_id,
                    'value' => $feature->value,
                ];
            }

            $featureArray[] = array_merge($featuredata, ['values' => $values]);
        }

        $this->requestArray = array_merge($this->requestArray, ['attributes' => $featureArray]);
    }

    /**
     * @return array
     */
    public function getRequestArray()
    {
        return $this->requestArray;
    }

}
