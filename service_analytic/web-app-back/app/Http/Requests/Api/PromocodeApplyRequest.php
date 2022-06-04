<?php

namespace App\Http\Requests\Api;

use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

use App\Repositories\Billing\PromocodeRepository;

/**
 * Class PromocodeApplyRequest
 * Settings validator
 *
 * @package App\Http\Requests\Api
 */
class PromocodeApplyRequest extends BaseRequest
{
    protected static $RULES = [
        'code' => 'required|regex:/^[a-z0-9]+$/i|exists:promocodes,code',
    ];

    /**
     * Проверка
     *
     * @param \Illuminate\Validation\Validator $validator Валидатор
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $code = (new PromocodeRepository)->getByCode($this->code);
        if ($code) {
            $this->basicValidation($validator, $code);
            $this->usageValidation($validator, $code);
        }
    }

    /**
     * Проверка что промокод активен и не устарел
     */
    protected function basicValidation($validator, $code)
    {
        $validator->after(
            function ($validator) use ($code) {
                if (!$code->active) {
                    $validator->errors()->add('code', 'Промокод не активен');
                }

                $now = Carbon::now();
                if ($code->start_at >= $now) {
                    $validator->errors()->add('code', 'Промокод пока не активен');
                }

                if ($code->end_at <= $now) {
                    $validator->errors()->add('code', 'Промокод уже не активен');
                }

                if ($code->end_at <= $now) {
                    $validator->errors()->add('code', 'Промокод уже не активен');
                }
            }
        );
    }

    /**
     * Проверка промокода на то что его ещё можно использовать
     */
    protected function usageValidation($validator, $code)
    {
        $validator->after(
            function ($validator) use ($code) {
                if ($code->usage_limit != -1) {
                    if ($code->promocodeUsers()->count() >= $code->usage_limit) {
                        $validator->errors()->add('code', 'Промокод уже использован максимальное число раз.');
                    }
                }
            }
        );
    }
}
