<?php

namespace App\Http\Requests;

use App\Models\WbCategory;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class WbProductRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $data = $this->input('data');

        if (!$this->input('object') || !$this->input('parent')) {
            return [
                'object' => 'required',
                'parent' => 'required',
            ];
        } else {
            $model = WbCategory::where(
                [
                    'name' => $this->input('object'),
                    'parent' => $this->input('parent'),
                ]
            )->first();

            $validation = [];

            if (isset($model->data)) {
                if (isset($model->data->addin) && isset($data['addin'])) {
                    // todo validation here
                    foreach ($data['addin'] as $key => $addin) {
                        if (isset($addin['params'][0]['value']) && $addin['params'][0]['value']) {
                            $validation['data.addin.' . $key . '.params.*.value'] = $this->modelAddinValidation($model->data->addin, $addin['type']);
                            $validation['data.addin.' . $key . '.params'] = $this->modelAddinCountValidation($model->data->addin, $addin['type'], 'data.addin.' . $key . '.params');
                        }
                        continue;
                        if (isset($addin['params'][0]['units']) && $addin['params'][0]['units']) {
                            $validation['data.addin.' . $key . '.params.*.count'] = $this->modelAddinValidation($model->data->addin, $addin['type']);
                        }
                    }
                }
            }

            // look task SE-1299
            $validation['data.addin.*.params'] = ['required', 'array', 'min:1'];
            $validation['user_id'] = ['required', 'integer'];
            $validation['account_id'] = ['required', 'integer'];
            $validation['imt_id'] = ['required', 'integer'];
            $validation['card_user_id'] = ['integer'];
            $validation['supplier_id'] = ['required', 'max:255'];
            $validation['title'] = ['required', 'max:255'];
            $validation['barcodes'] = ['required', 'array'];
            $validation['nmid'] = ['required'];
            $validation['sku'] = ['required'];
            $validation['price'] = ['required', 'numeric'];
            $validation['object'] = ['required', 'max:255'];
            $validation['parent'] = ['required', 'max:255'];
            $validation['country_production'] = ['required', 'max:255'];
            $validation['supplier_vendor_code'] = ['max:255'];

            return $validation;
        }


    }

    /**
     * Validation addin params (for required and numeric)
     * @param array $addins
     * @param $type
     */
    private function modelAddinValidation($addins, $type)
    {
        $v = [];

        foreach ($addins as $addin) {
            if ($addin->type === $type) {
                if (isset($addin->required) && $addin->required) {
                    $v[] = 'required';
                }
                if (isset($addin->isNumber) && $addin->isNumber) {
                    $v[] = 'numeric';
                }
            }
        }
        return $v;
    }

    /**
     * Validation count params
     * @param $addins
     * @param $type
     * @param $key
     * @return array
     */
    protected function modelAddinCountValidation($addins, $type, $key)
    {
        $v = [];

        foreach ($addins as $addin) {
            if ($addin->type === $type) {
                if (isset($addin->maxCount) && $addin->maxCount) {
                    $v[] = [$key => 'array|max:' . $addin->maxCount];
                }
            }
        }

        return $v;
    }
}
