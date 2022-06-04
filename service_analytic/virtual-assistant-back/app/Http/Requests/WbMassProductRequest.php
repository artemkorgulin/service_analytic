<?php

namespace App\Http\Requests;

use App\Models\WbCategory;

class WbMassProductRequest extends WbProductRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $products = $this->input('products');
        $notIssetCategory = false;
        foreach ($products as $product) {
            if (!isset($product['object']) || !isset($product['parent'])) {
                $notIssetCategory = true;
            }
        }

        if ($notIssetCategory) {
            return [
                'products.object' => 'required',
                'products.parent' => 'required',
            ];
        } else {
            $iterator = 0;
            foreach ($products as $product) {
                $data = $product['data'];
                $wbCategory = WbCategory::where(
                    [
                        'name' => $this->input('object'),
                        'parent' => $this->input('parent'),
                    ]
                )->first();

                $validation = [];

                if (isset($wbCategory->data)) {
                    if (isset($wbCategory->data->addin) && isset($data['addin'])) {
                        // todo validation here
                        foreach ($data['addin'] as $key => $addin) {
                            if (isset($addin['params'][0]['value']) && $addin['params'][0]['value']) {
                                $validation['products.' . $iterator . '.data.addin.' . $key . '.params.*.value'] = $this->modelAddinValidation($wbCategory->data->addin, $addin['type']);
                                $validation['products.' . $iterator . '.data.addin.' . $key . '.params'] = $this->modelAddinCountValidation($wbCategory->data->addin, $addin['type'], 'data.addin.' . $key . '.params');
                            }
                        }
                    }
                }
                $iterator++;
            }

            $validation['products.*.data.addin.*.params'] = ['required', 'array', 'min:1'];
            $validation['user_id'] = ['required', 'integer'];
            $validation['account_id'] = ['required', 'integer'];
            $validation['products.*.imt_id'] = ['required', 'integer'];
            $validation['products.*.card_user_id'] = ['integer'];
            $validation['products.*.supplier_id'] = ['required', 'max:255'];
            $validation['products.*.title'] = ['required', 'max:255'];
            $validation['products.*.barcodes'] = ['nullable', 'array'];
            $validation['products.*.nmid'] = ['required'];
            $validation['products.*.sku'] = ['required'];
            $validation['products.*.price'] = ['required', 'numeric'];
            $validation['products.*.object'] = ['required', 'max:255'];
            $validation['products.*.parent'] = ['required', 'max:255'];
            $validation['products.*.country_production'] = ['required', 'max:255'];
            $validation['products.*.supplier_vendor_code'] = ['max:255'];

            return $validation;
        }
    }
}
