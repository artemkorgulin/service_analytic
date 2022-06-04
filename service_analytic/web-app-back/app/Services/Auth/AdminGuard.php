<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class AdminGuard extends \Illuminate\Auth\SessionGuard
{

    public function __construct(UserProvider $provider, Session $session, Request $request = null)
    {
        parent::__construct('admin', $provider, $session, $request);
    }


    /**
     * @param  User  $user
     * @param  array  $credentials
     *
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $user->hasRole('admin') && parent::hasValidCredentials($user, $credentials);
    }
}
