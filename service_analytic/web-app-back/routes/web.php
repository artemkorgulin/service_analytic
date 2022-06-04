<?php

use App\Http\Controllers\ProxyWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes from admin panel
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return redirect('/vt/login');
})->name('login');

Route::get('/logout', function () {
    return redirect('/vt/logout');
})->name('logout');

Route::middleware('admin')->group(function () {
    Route::get('/vt/impersonation/take/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'take'])
        ->name('admin.impersonation.take');
    Route::get('/vt/impersonation/leave', [\App\Http\Controllers\Admin\ImpersonationController::class, 'leave'])
        ->name('admin.impersonation.leave');
});

Route::group(['prefix' => 'nova-api', 'middleware' => 'nova'], function () {
    Route::post('accounts', [\App\Nova\Controllers\AccountController::class, 'store']);
    Route::delete('accounts', [\App\Nova\Controllers\AccountController::class, 'destroy']);
});

Route::get('vp/download/{name}/{id}', ProxyWebController::class);
