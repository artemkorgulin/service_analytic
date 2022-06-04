<?php


namespace App\Http\Controllers\Api\inner;


use App\Models\Platform;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use AnalyticPlatform\LaravelHelpers\Constants\Errors\AuthErrors;


/**
 * Class AuthController
 * Контроллер с методами авторизации пользователей
 * @package App\Http\Controllers\Api\v1
 */
class InnerCommunicationController extends Controller
{

    /**
     * Получение всех платформ
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPlatforms()
    {
        return response()->json(Platform::all());
    }

    /**
     * Получение всех пользователей с аккаунтами
     */
    public function getAllUsersAndAccounts()
    {
        return response()->json(User::with('accounts')->orderBy('id')->get());
    }

    /**
     * Получение аккаунта по id
     */
    public function getAccount($id)
    {
        $account = Account::where('id', $id)->first();
        if (!$account) {
            return response()->api_fail(
                'Указанный аккаунт не найден',
                [],
                422,
                AuthErrors::USER_NOT_FOUND
            );
        }
        $account->user_id = $account->userId();
        return response()->json($account);
    }

    /**
     * Deactivate account by client ID (platform_client_id)
     *
     * @param string $supplierId
     * @return JsonResponse - array of accounts with users
     */
    public function deactivateAccount(string $supplierId): JsonResponse
    {
        $accounts = Account::where([
            'platform_client_id' => $supplierId,
            'platform_api_key' => request()->input('api_key', ''),
            'deleted_at' => null
        ])->get();
        if ($accounts->isEmpty()) {
            return response()->api_fail('Нет аккаунтов с указанными реквизитами', [], 404);
        }
        $result = [];
        foreach ($accounts as $account) {
            $account->deleted_at = now();
            $account->save();
            $users = User::where('id', $account->userId())->get();
            if (!$users->isEmpty()) {
                $result [] = [
                    'account' => $account,
                    'users' => $users
                ];
            }
        }
        return response()->json($result);
    }

    /**
     * Получение всех активных аккаунтов
     */
    public function getAllAccounts()
    {
        return response()->json(Account::all());
    }

    /**
     * Получение всех активных аккаунтов adm
     */
    public function getAllAdmAccounts()
    {
        return response()->json(Account::getAllAdmAccounts());
    }

    /**
     * Получение всех активных аккаунтов Ozon
     */
    public function getAllSellerAccounts($id)
    {
        $accounts = [];
        foreach (Account::where('platform_id', $id)->get() as $account) {
            $account['user_id'] = $account->userId();
            $accounts[] = $account;
        }
        return response()->json($accounts);
    }

    /**
     * Получение всех активных аккаунтов ozon seller
     */
    public function getAllSellerOzonAccounts()
    {
        return response()->json(Account::getAllSellerOzonAccounts());
    }

    /**
     * Получение всех активных аккаунтов ozon seller
     */
    public function getAllSellerWildberriesAccounts()
    {
        return response()->json(Account::getAllSellerWildberriesAccounts());
    }

}
