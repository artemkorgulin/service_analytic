<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;

/**
 * Class UserController
 * Контроллер для управления пользователями
 * @package App\Http\Controllers\Api\v1
 */
class UserController extends Controller
{
    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api_v1');
    }

    /**
     * Deactivate user
     *
     * @param int $userID
     * @return User
     */
    public function deactivateUser(int $userID): User
    {
        $user = User::find($userID);
        $user->is_active = 0;
        $user->save();
        return $user;
    }
}
