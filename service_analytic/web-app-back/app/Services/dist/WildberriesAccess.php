<?php

namespace App\Services\dist;

use App\Contracts\AccessInterface;
use App\Contracts\Api\WildberriesApiInterface;
use App\Http\Requests\Api\SettingsRequest;
use App\Models\User;
use Exception;

/**
 *
 */
class WildberriesAccess extends Access implements AccessInterface
{
    /**
     * заголовок аккаунта
     */
    const TITLE = 'Wildberries';

    /**
     * Описание аккаунта
     */
    const DESCRIBE = 'Wildberries';

    /**
     * @param SettingsRequest $request
     * @return WildberriesAccess
     */
    public function setRequest(SettingsRequest $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * валидация wildberries.ru api ключей
     * @throws Exception
     */
    public function valid(): bool
    {

        if (empty($this->request->client_id)) {
            throw new Exception('Ошибка подключения к Wildberries API - не введен API токен');
        }

        if (empty($this->request->client_api_key)) {
            throw new Exception('Ошибка подключения к Wildberries API, проверьте правильность введенных данных');
        }

        $WildberriesApi = \App::make(
            WildberriesApiInterface::class,
            ['clientId' => $this->request->client_id, 'apiKey' => $this->request->client_api_key]
        );


        if(!$WildberriesApi->validateAccessData()) {
            throw new Exception('Ошибка подключения к Wildberries API, неверный API токен');
        }
        if(!$WildberriesApi->validateAccessDataForClientId()) {
            throw new Exception('Ошибка подключения к Wildberries API, неверный Ключ для работы с API статистики x32 WB');
        }

        return true;
    }

    public static function setRole(User $user){
        $user->assignRole( 'wildberries.supplier');
    }

    /**
     * @throws Exception
     */
    public function run(){
        if(!$this->valid())
            throw new Exception('Токен и ключ не прошли проверку');
        return $this->save();
    }
}
