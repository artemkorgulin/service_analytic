<?php

namespace App\Services;

use App\Contracts\AccessInterface;
use App\Http\Requests\Api\SettingsRequest;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

class AccessService
{
    private static $platform = [
      '1' =>  \App\Services\dist\OzonSellerAccess::class,
      '2' =>  \App\Services\dist\OzonPerformanceAccess::class,
      '3' =>  \App\Services\dist\WildberriesAccess::class,
    ];

    /**
     * @var AccessInterface
     */
    private AccessInterface $strategy;
    private SettingsRequest $request;

    public function __construct(){

    }

    public function setPlatform(){
        $this->strategy = new self::$platform[$this->request->platform_id]();
    }

    /**
     * @param  SettingsRequest  $request
     * @return AccessService
     * @throws Exception
     */
    public function setRequest(SettingsRequest $request): self
    {
        if(!$request->platform_id)
            throw new Exception('Не передан id нужного маркетплейса');
        $this->request = $request;
        return $this;
    }

    public function run(?Authenticatable $user){
        $this->setPlatform();
        if ($user) {
            $this->setUser($user);
        }
        return $this->strategy->setRequest($this->request)->run();
    }


    /**
     * @param  Authenticatable  $user
     *
     * @return AccessService
     * @throws Exception
     */
    protected function setUser(Authenticatable $user): self
    {
        if (!$this->strategy) {
            throw new Exception('Не выбран маркетплейс');
        }

        $this->strategy->setUser($user);

        return $this;
    }
}
