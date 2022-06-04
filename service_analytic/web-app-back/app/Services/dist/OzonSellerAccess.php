<?php

namespace App\Services\dist;

use App\Contracts\AccessInterface;
use App\Http\Requests\Api\SettingsRequest;
use App\Models\User;
use App\Contracts\Api\OzonApiInterface;
use Exception;


class OzonSellerAccess extends Access implements AccessInterface
{
    const TITLE = 'Ozon Seller';
    const DESCRIBE = 'Ozon Seller';

    /**
     * Set roles to user
     * @param  User  $user
     */
    public static function setRole(User $user)
    {
        $user->assignRole('ozon.seller.supplier');
    }

    /**
     * @param  SettingsRequest  $request
     * @return OzonSellerAccess
     * @throws Exception
     */
    public function setRequest(SettingsRequest $request): self
    {
        if (empty($request->client_id) || empty($request->client_api_key) || empty($request->platform_id)) {
            throw new Exception('Ошибка подключения к Ozon API, проверьте правильность введенных данных');
        }
        $this->request = $request;
        return $this;
    }

    /**
     * Run validation
     * @throws Exception
     */
    public function run()
    {
        if (!$this->valid()) {
            throw new Exception('Токен и ключ не прошли проверку');
        }

        return $this->save();
    }

    /**
     * Validation
     * @throws Exception
     */
    public function valid(): bool
    {
        $ozonApi = \App::make(
            OzonApiInterface::class,
            ['clientId' => $this->request->client_id, 'apiKey' => $this->request->client_api_key]
        );

        if ($ozonApi->validateAccessData()) {
            return true;
        }
        return false;
    }


}
