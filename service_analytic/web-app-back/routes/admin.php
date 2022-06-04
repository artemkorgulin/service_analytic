<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\AccountController;
use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminTariffController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->group(function () {
    Route::group(['middleware' => ['guest:admin']], function () {
        Route::get('/login', [LoginController::class, 'index'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login');
    });

    Route::group(['middleware' => ['auth:admin', 'cors']], function () {
        Route::any('/logout', function () {
            Auth::guard('admin')->logout();
        })->name('logout');
        Route::get('/get-menu', [AppController::class, 'getMenu'])->name('getMenu');
        Route::get('/dashboard', [AppController::class, 'index'])->name('dashboard');

        // Методы для ролей
        Route::get('get-roles', [AccountController::class, 'getRoles']);
        Route::get('get-role/{roleId}', [AccountController::class, 'getRole']);
        Route::post('add-roles', [AccountController::class, 'addRoles']);
        Route::get('get-models', [AccountController::class, 'getModels']);
        Route::patch('change-roles-user/{userId}', [AccountController::class, 'changeRolesUser']);

        // Методы для тарифов
        Route::apiResource('tariffs', AdminTariffController::class);

        // Методы для редактирования пользователя
        Route::post('add-user', [AuthController::class, 'addUser']);
        Route::patch('edit-user/{userId}', [AccountController::class, 'editUser']);
        Route::get('get-user/{userId}', [AccountController::class, 'getUser']);
        Route::get('get-all-users', [AccountController::class, 'getAllUsers']);
        Route::patch('change-active-user/{userId}', [AccountController::class, 'changeActiveUser']);
        Route::patch('change-password-user/{userId}', [AccountController::class, 'changePasswordUser']);
        Route::delete('delete-user/{userId}', [AuthController::class, 'deleteUser']);

        //Права пользователя
        Route::post('users/permissions', [UserController::class, 'permissionsList']);
        Route::get('get-all-permissions', [AccountController::class, 'getAllPermission'])->name('get-all-permissions');
        Route::get('get-permissions-role/{roleId}', [AccountController::class, 'getPermissionsRole']);
        Route::patch('patch-permissions-role/{roleId}', [AccountController::class, 'patchPermissionsRole']);

        //Необходимо для перенаправления на Vue Router если пользователь обновляет страницу и идёт запрос к серверу
        Route::get('{any}', [AppController::class, 'index'])->where('any', '.*');
    });
});
