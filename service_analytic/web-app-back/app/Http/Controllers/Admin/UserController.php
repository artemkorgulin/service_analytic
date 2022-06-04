<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Response;

class UserController extends Controller
{
    /**
     * Show users journal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Store new user
     *
     * @param  UserRequest  $request
     * @throws Exception
     */
    public function store(UserRequest $request)
    {
        /** @var User $user */
        $user = ModelHelper::transaction(function () use ($request) {
            $user = new User($request->all());
            $user->first_login = true;
            $user->save();

            return $user;
        });

        return Response::json([
            'user' => $user->getAttributes()
        ]);
    }

    /**
     * Update user
     *
     * @param  UserRequest  $request
     * @param  User  $user
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        /** @var User $updateUser */
        $updateUser = ModelHelper::transaction(function () use ($request, $user) {
            $user = $user->fill($request->all());
            $user->save();

            return $user;
        });

        (new UserService($user))->forgetAllCache();

        return Response::json([
            'user' => $updateUser->getAttributes()
        ]);
    }

    /**
     * Delete user
     *
     * @param  Request  $request
     * @throws Exception
     */
    public function destroy(Request $request, User $user)
    {
        ModelHelper::transaction(function () use ($user) {
            $user->delete();
        });

        (new UserService($user))->forgetAllCache();

        return Response::json([
            'message' => 'Пользователь успешно удалён'
        ]);
    }
}
