<?php

namespace App\Services\dist;

use App\Contracts\AccessInterface;
use App\Http\Requests\Api\SettingsRequest;
use App\Models\User;
use App\Services\V2\OzonPerformanceApi;
use Exception;

class OzonPerformanceAccess extends Access implements AccessInterface
{
    const TITLE = 'Ozon Performance';
    const DESCRIBE = 'Ozon Performance';

    /**
     * @param  SettingsRequest  $request
     */
    public function setRequest(SettingsRequest $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * валидация ozon.ru performance ключей
     * @throws Exception
     */
    public function valid(): bool
    {
        if (empty($this->request->client_api_key) || empty($this->request->client_id)) {
            throw new Exception('Ошибка подключения к Ozon API, проверьте правильность введенных данных');
        }
        if((new OzonPerformanceApi())->validatePerformanceKeys($this->request->client_id, $this->request->client_api_key))
            return true;
        return false;
    }

    public static function setRole(User $user){
        $user->assignRole( 'ozon.performance.supplier');;
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
