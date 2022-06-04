<?php


namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Str;

trait UserForTest
{
    public function getUser()
    {
        $check = User::whereNotNull('api_token');

        if ($check->exists()) {
            $user = $check->first();
        } else{
            $user = User::find(1);
            $user->api_token = Str::random(60);
            $user->save();
        }

        return $user;
    }

    public function getToken()
    {
        return $this->getUser()->api_token;
    }

    /**
     * Get header from inner request
     *
     * @return array
     */
    public function getInnerRequestHeader(): array
    {
        return [
            'Authorization-Web-App' => config('auth.slave_app_token'),
            'Content-Type' => 'application/json'
        ];
    }
}

