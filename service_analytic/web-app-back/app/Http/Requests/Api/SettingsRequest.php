<?php

namespace App\Http\Requests\Api;

use App\Models\Account;
use App\Rules\CompanyHasUser;
use App\Rules\PhoneRule;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Class SettingsRequest
 * Settings validator
 *
 * @property string $platform_id
 * @property string $client_id equivalent of platform_client_id in Account model
 * @property string $client_api_key equivalent of platform_api_key in Account model
 *
 * @package App\Http\Requests\Api
 */
class SettingsRequest extends BaseRequest
{
    protected static $RULES = [
        'name' => 'nullable|string|min:4',
        'password' => 'nullable|string|min:8',
        'tariff_phone_modal_shown' => 'nullable|boolean',
        'phone' => ['nullable'],
        'accounts' => 'array|nullable',
        'accounts.*.platform_id' => 'string|nullable',
        'accounts.*.platform_client_id' => 'string|nullable',
        'accounts.*.platform_api_key' => 'string|nullable'
    ];

    public function rules(): array
    {
        $existAccountId = !empty(request('account_id')) ? request('account_id') : null;
        Log::info('Запрос валидации SettingsRequest ', $this->request->all());

        if (!empty(request('email'))) {
            self::$RULES['email'] = 'nullable|string|email:rfc,dns|unique:users,email,' . Auth::user()->id . ',id';
        }

        $accounts = Account::where(['platform_client_id' => $this->request->get('client_id')])->get()->all();
        $accountsPermissions = [];

        if (!empty($accounts)) {
            foreach ($accounts as $account) {
                $accountsPermissions = array_merge(
                    $accountsPermissions,
                    array_column($account->getPermissionsViaRoles()->toArray(), 'name')
                );
            }

            if (!in_array('multiply.accounts.add', $accountsPermissions) && !$existAccountId) {
                self::$RULES['client_id'] = [
                    'nullable',
                    'string',
                    Rule::unique('accounts', 'platform_client_id')->whereNull('deleted_at'),
                ];
            } else {
                self::$RULES['client_id'] = 'nullable|string';
            }
            if (!in_array('multiply.users.bind.account', $accountsPermissions) && !$existAccountId) {
                self::$RULES['client_api_key'] = [
                    'nullable',
                    'string',
                    Rule::unique('accounts', 'platform_api_key')->whereNull('deleted_at'),
                ];
            } else {
                self::$RULES['client_api_key'] = 'nullable|string';
            }
        } else {
            self::$RULES['client_id'] = [
                'nullable',
                'string',
                Rule::unique('accounts', 'platform_client_id')->whereNull('deleted_at'),
            ];
            self::$RULES['client_api_key'] = [
                'nullable',
                'string',
                Rule::unique('accounts', 'platform_api_key')->whereNull('deleted_at'),
            ];
        }

        if ($this->company_id) {
            self::$RULES['company_id'] = [
                'nullable',
                'integer',
                new CompanyHasUser(auth()->user()->id),
            ];
        }

        self::$RULES['phone'][] = new PhoneRule();
        self::$RULES['phone'][] = Rule::unique('users')->ignore($this->user()->id);
        return static::$RULES;
    }

    public function messages(): array
    {
        return [
            'client_id.unique' => 'Client ID занят другим пользователем. Использовать одинаковые аккаунты API нельзя.',
            'client_api_key.unique' => 'API key занят другим пользователем. Использовать одинаковые аккаунты API нельзя.',
        ];
    }
}