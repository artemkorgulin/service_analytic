<?php

namespace App\Http\Requests\Api;

use App\Helpers\PaymentHelper;
use App\Rules\IsCompanyOwner;
use Illuminate\Foundation\Http\FormRequest;

use App\Models\Service;
use App\Models\Tariff;
use App\Repositories\Billing\ServiceRepository;
use App\Rules\CompanyRole;

class BillingOrderRequest extends BillingCheckPriceRequest
{
    protected static $ATTRIBUTES = [
        'tariff_id' => 'Тариф',
        'duration' => 'Продолжительность бронирования',
        'services' => 'Набор услуг',
        'paymentMethod' => 'Метод оплаты',
        'company_id' => 'Идентификатор компании',
    ];

    /**
     * Правила валидации
     */
    public function rules()
    {
        $result = [
            'tariff_id' => 'nullable|integer',
            'duration' => 'required|integer|in:1,3,6,12',
            'services' => 'nullable|array',
            'services.*.id' => 'integer',
            'services.*.amount' => 'nullable|integer',
            'services.*.alias' => 'required|string',
            'paymentMethod' => 'required|in:card,bank',
            'company' => 'nullable|array',
            'company.inn' => ['required_with:company', 'string'],
            'company.kpp' => 'nullable|string',
            'company.name' => 'nullable|string',
            'company.address' => 'nullable|string',
            'company_id' => [ 'nullable', 'integer', 'exists:companies,id' ],
        ];

        if (PaymentHelper::serviceIsset($this->services, 'corp')) {
            $result['company.inn'][] = new IsCompanyOwner(auth()->user()->id);
        }

        return $result;
    }

    /**
     * Дополнительные проверки логики запроса
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateNotBoth($validator, 'company', 'company_id');

            if (!$this->hasErrorOnCompany($validator)) {
                $this->companyPresenceForBankPayment($validator);
                $this->companyPresenceForCorpAccess($validator);
            }
        });
    }

    /**
     * Только одно поле из двух должно быть
     */
    private function validateNotBoth($validator, $fieldA, $fieldB)
    {
        if ($this->$fieldA and $this->$fieldB) {
            $validator->errors()->add($fieldA, 'Необходимо использовать либо поле с идентификатором, либо с данными');
            $validator->errors()->add($fieldB, 'Необходимо использовать либо поле с идентификатором, либо с данными');
        }
    }

    /**
     * Плательщик должен быть указан для банковского платежа
     */
    private function companyPresenceForBankPayment($validator)
    {
        if ($this->paymentMethod === 'bank') {
            if ($this->missedCompanyInformation()) {
                $validator->errors()->add('paymentMethod', 'При оплате через банк - нужно передать информацию о плательщике');
            }
        }
    }

    /**
     * Проверка на присутствие информации по компании в запросе,
     * если заказывается корпоративный доступ
     */
    private function companyPresenceForCorpAccess($validator)
    {
        if ($this->hasCorpAccess()) {
            if ($this->missedCompanyInformation()) {
                $validator->errors()->add('company', 'Не передана информация о компании для оформления корпоративного доступа');
            }
        }
    }

    /**
     * Отсутствует ли информация по компании в запросе
     */
    private function missedCompanyInformation()
    {
        return (!$this->company and !$this->company_id);
    }

    /**
     * Проверка - есть ли ошибки по компании, чтобы не прогонять свои спец-проверки
     */
    private function hasErrorOnCompany($validator)
    {
        return $validator->errors()->hasAny(['company_id', 'company']);
    }

    /**
     * Проверка - есть ли корпоративный доступ в списке услуг
     */
    private function hasCorpAccess()
    {
        if (!$this->services) {
            return false;
        }

        $service = (new ServiceRepository())->findByAlias(Service::ALIAS_CORP);
        foreach ($this->services as $serviceOrder) {
            if ($serviceOrder['id'] == $service->id) {
                return true;
            }
        }
    }
}
