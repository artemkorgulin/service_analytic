<?php
namespace App\Contracts;

use App\Http\Requests\Api\SettingsRequest;
use Illuminate\Contracts\Auth\Authenticatable;

interface AccessInterface
{
    /**
     * @param  SettingsRequest  $request
     * @return AccessInterface
     */
    public function setRequest(SettingsRequest $request): AccessInterface;

    /**
     * @return bool
     */
    public function valid(): bool;


    /**
     * @return mixed
     */
    public function run();

    /**
     * @param  Authenticatable  $user
     *
     * @return void
     */
    public function setUser(Authenticatable $user);
}
