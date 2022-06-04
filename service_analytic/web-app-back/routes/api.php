<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\AccountController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BrandsController;
use App\Http\Controllers\Api\v1\CompanyController;
use App\Http\Controllers\Api\v1\GoodsController;
use App\Http\Controllers\Api\v1\MailController;
use App\Http\Controllers\Api\v1\PDFController;
use App\Http\Controllers\Api\v1\ProxyController;
use App\Http\Controllers\Api\v1\OldTariffController;
use App\Http\Controllers\Api\v1\PromocodeController;

use App\Http\Controllers\Api\v2\BillingController;
use App\Http\Controllers\Api\v2\OrderController;
use App\Http\Controllers\Api\v2\TariffController;
use App\Http\Controllers\Api\v2\ServiceController;

use App\Http\Controllers\Api\v1\UserCompanyController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\Yookassa\YookassaController;
use App\Http\Controllers\Api\inner\InnerCommunicationController;
use App\Http\Controllers\Api\inner\InnerCountersController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('pdf/download', [PDFController::class, 'downloadPDF'])->name('pdf.download');
Route::get('pdf/generate', [PDFController::class, 'generatePDF'])->name('pdf.generate');
Route::post('merchant/hook-url', [YookassaController::class, 'handleEvent']);
Route::get('inner/lending-users-count', [InnerCountersController::class, 'getLendingCountUsers']);

Route::group(['prefix' => 'inner', 'middleware' => ['web_app']], function () {
    Route::get('get-all-accounts/{platformId?}', [AccountController::class, 'getAllAccounts']);
    Route::get('get-all-users-and-accounts', [InnerCommunicationController::class, 'getAllUsersAndAccounts']);
    Route::get('platforms', [InnerCommunicationController::class, 'getAllPlatforms']);
    Route::get('adm-accounts/{platformId?}', [InnerCommunicationController::class, 'getAllAdmAccounts']);
    Route::get('vp-accounts/{platformId?}', [InnerCommunicationController::class, 'getAllSellerAccounts']);
    Route::get('accounts/{id}', [InnerCommunicationController::class, 'getAccount']);
    Route::post('accounts/{clientId}/users', [InnerCommunicationController::class, 'deactivateAccount']);
    Route::get('user/{id}/deactivate', [UserController::class, 'deactivateUser']);
});

// Методы для авторизации
Route::group(['prefix' => 'v1', 'middleware' => ['cors']], function () {
    Route::post('sign-in', [AuthController::class, 'login']);
    Route::post('sign-up', [AuthController::class, 'registration']);
    // Проверка мейла для пользователя
    Route::any('sign-up-confirm', [AuthController::class, 'confirmRegistration']);
    Route::post('reset-password-request', [AuthController::class, 'resetPasswordRequest']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::apiResource('/users/{user}/accounts', AccountController::class);
    // Подтверждение номера телефона
    Route::group(['prefix' => 'phone'], function () {
        Route::post('confirm', [AuthController::class, 'phoneConfirm'])
            ->middleware(config('feature.phone-login-throttle') ? 'throttle:10,1,phone-confirm' : null);
        Route::post('send-code', [AuthController::class, 'phoneSendCode'])
            ->middleware(config('feature.phone-login-throttle') ? 'throttle:3,1,phone-send-code' : null);
    });
});

Route::group(['prefix' => 'v2',  'middleware' => ['auth:api_v1', 'cors']], function () {
    // Методы билинга 2022 года
    Route::group(['prefix' => 'billing'], function () {
        Route::get('/information', [BillingController::class, 'information']);
        Route::get('/discounts', [BillingController::class, 'discounts']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/check-final-price', [OrderController::class, 'checkFinalPrice']);
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{id}', [ServiceController::class, 'show']);
        Route::get('/tariffs', [TariffController::class, 'index']);
        Route::get('/tariffs/{id}', [TariffController::class, 'show']);
    });
});

// Методы для авторизованных пользователей
Route::group(['prefix' => 'v1',  'middleware' => ['auth:api_v1', 'cors']], function () {
    Route::get('get-user-permissions/{userId}',
        [AccountController::class, 'getUserPermission'])->name('get-user-permissions');
    Route::get('get-all-permissions', [AccountController::class, 'getAllPermission'])->name('get-all-permissions');

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Методы для доступа к товарам пользователя
    Route::get('get-goods-user-oz/{userId}/{accountId?}', [GoodsController::class, 'getGoodsUserOz']);
    Route::get('get-goods-user-wb/{userId}/{accountId?}', [GoodsController::class, 'getGoodsUserWb']);

    Route::get('get-black-list', [GoodsController::class, 'getBlackList']);
    Route::patch('black-list/edit/{id}', [GoodsController::class, 'editBlackList']);

    Route::patch('transfer-accounts', [AccountController::class, 'transferAccounts']);

    //Компания
    Route::resource('company', CompanyController::class);

    Route::post('user-company', [UserCompanyController::class, 'store']);
    Route::delete('user-company', [UserCompanyController::class, 'destroy']);
    Route::get('user-search', [UserCompanyController::class, 'search']);

    Route::get('company-roles', [UserCompanyController::class, 'getAllRoles']);

    Route::get('user-company-roles/{company}/{user}', [UserCompanyController::class, 'userRoles']);
    Route::post('user-company-roles/{company}/{user}', [UserCompanyController::class, 'userSetRole']);
    Route::delete('user-company-roles/{company}/{user}', [UserCompanyController::class, 'userDeleteRole']);

    Route::post('set-settings', [AuthController::class, 'setSettings']);
    Route::post('set-settings/password', [AuthController::class, 'password']);
    Route::get('get-settings', [AuthController::class, 'getSettings']);


    // Биллинг
    Route::patch('change-user-tariff/{userId}', [OldTariffController::class, 'changeUserTariff']);
    Route::post('tariffs', [OldTariffController::class, 'subscribe']);
    Route::post('tariffs/{tariffId}', [OldTariffController::class, 'subscribe']);
    Route::get('tariffs', [OldTariffController::class, 'index']);
    Route::get('history', [OldTariffController::class, 'getHistoryPayments']);
    Route::post('yookassa/receipts', [YookassaController::class, 'handleEvent']);
    Route::post('merchant/hook-url', [YookassaController::class, 'handleEvent']);

    // Промокоды
    Route::get('promocodes', [PromocodeController::class, 'index']);
    Route::post('promocodes/apply', [PromocodeController::class, 'applyCode']);


    Route::get('get-all-accounts/{platformId?}', [AccountController::class, 'getAllUserAccounts']);
    Route::get('get-all-available-accounts', [AccountController::class, 'getAllAvailableUserAccounts']);
    Route::post('set-default-account', [AccountController::class, 'setDefaultAccount']);
    Route::post('set-default-company', [AccountController::class, 'setDefaultCompany']);

    //Методы для управления маркетплейсами
    Route::get('get-platforms', [AccountController::class, 'getPlatforms']);
    Route::patch('change-platforms/{userId}', [AccountController::class, 'changePlatforms']);
    // todo нужен только для установки аккаунтов пользователей

    //Взаимодействие с брендами
    Route::get('get-brands/{search?}', [BrandsController::class, 'getBrands']);
    Route::get('get-brand/{id}', [BrandsController::class, 'getBrand']);
    Route::get('get-brand-all/{id}', [BrandsController::class, 'getBrandAll']);
    Route::patch('patch-brand/{id}', [BrandsController::class, 'patchBrand']);
    Route::delete('/black-list/delete/{id}', [BrandsController::class, 'deleteBrand']);
    Route::post('/black-list/create', [BrandsController::class, 'createBrand']);


    Route::get('get-all-users-and-accounts', [AccountController::class, 'getAllUsersAndAccounts']);
    Route::get('get-all-adm-accounts/{platformId?}', [AccountController::class, 'getAllAdmAccounts']);
    Route::get('get-all-vp-accounts/{platformId?}', [AccountController::class, 'getAllSellerAccounts']);
    Route::get('accounts/{id}', [AccountController::class, 'getAccount']);
    Route::post('set-access', [AccountController::class, 'setAccess']);
    Route::post('edit-access', [AccountController::class, 'editAccess']);
    // Генерация PDF документов
    Route::get('/pdf/generate', [PDFController::class, 'generatePDF']);
    Route::post('/pdf/download', [PDFController::class, 'downloadPDF']);
    Route::post('/pdf/preview', [PDFController::class, 'previewInvoice']);

    //запрос на оптимизацию товаров
    Route::get('/send-mail', [MailController::class, 'send']);
    Route::post('/send-mail', [MailController::class, 'send']);
});

// Проксирование запросов на другие backend сервера
Route::middleware(['auth:api_v1', 'cors'])->group(function () {
    Route::any('adm/{version}/{name}', [ProxyController::class, 'proxy'])->where('name', '(.*)');
    Route::any('an/{version}/{name}', [ProxyController::class, 'proxy'])->where('name', '(.*)');
    Route::any('event/{version}/{name}', [ProxyController::class, 'proxy'])->where('name', '(.*)');
    Route::any('vp/{version}/{name}', [ProxyController::class, 'proxy'])->where('name', '(.*)');
});
