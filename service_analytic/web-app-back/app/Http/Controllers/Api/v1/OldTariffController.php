<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\YooKassa\YooKassaApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubscriptionRequest;
use App\Models\OldTariff;
use App\Models\Order;
use App\Models\TariffActivity;
use App\Services\CalculateService;
use App\Services\CompanyService;
use App\Services\UserService;
use App\Services\V2\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class OldTariffController
 * Контроллер для управления тарифами
 * @package App\Http\Controllers\Api\v1
 */
class OldTariffController extends Controller
{
    const BANK = 'bank';
    const CARD = 'card';

    /**
     * Список актуальных тарифов
     * @return mixed
     */
    public function index()
    {
        $tariffs = OldTariff::visible()->get()->toArray();
        $resTariffs = [];

        $user = auth()->user();
        $userPromocode = $user->haveUnusedDiscountPromocode() ?  $user->nextDiscountPromocode() : null;
        foreach ($tariffs as $tariff) {
            if (strripos($tariff['description'], '|')) {
                $tariff['description'] = explode('|', $tariff['description']);
            }

            $tariff['discounted_prices'] = CalculateService::discountsForTariff(
                $tariff['id'],
                $userPromocode
            );

            $resTariffs[] = $tariff;
        }

        return response()->api_success($resTariffs, 200);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function changeUserTariff(Request $request): JsonResponse
    {
        if (empty($request->userId)) {
            throw new Exception('Не передан id пользователя');
        }

        if ($tariff = TariffActivity::query()->where([
            'user_id' => $request->userId, 'status' => TariffActivity::ACTVE
        ])->first()) {
            $tariff->tariff_id = $request->tariff;
            $tariff->save();
        }

        (new UserService())->forgetTariffCache();

        return response()->json('Сменен тариф пользователя');
    }

    /**
     * Оформить подписку
     *
     * @param  SubscriptionRequest  $request
     * @return mixed
     * @return  JsonResponse
     * @throws YooKassaApiException
     *
     */
    public function subscribe(SubscriptionRequest $request): JsonResponse
    {
        $user = auth()->user();
        $paymentService = new PaymentService($user);
        $data = $request->all();
        $data['tariff_ids'] = collect($data['tariff_id'])->toJson();

        $userPromocode = $user->haveUnusedDiscountPromocode() ?
            $user->nextDiscountPromocode() : null;

        $data['amount'] = CalculateService::calculatePriceTariffsByDiscount(
            $data['tariff_id'],
            $data['period'],
            $userPromocode
        );

        $data['currency'] = "RUB";
        $data['status'] = "pending";
        $order = new Order();
        $data['order'] = $order->saveOrder($data);
        if ($userPromocode) {
            $userPromocode->makeUsedForOrder($order);
        }

        $data['tariffsCollection'] = OldTariff::whereIn('tariff_id', $data['tariff_id'])->get();
        $userService = new UserService();

        if ($request->get('paymentMethod') === self::BANK) {
            // Оплата по счету
            $paymentService->generateOrderInBank($data);
            // Сохранение company
            $companyService = new CompanyService;
            $company = $companyService->findOrCreateCompany($data['company']);
            $companyService->adoptCompany($company, auth()->user());
            $userService->forgetTariffCache();

            return response()->api_success([
                'orderId' => $paymentService->order->id,
            ], 201);
        } elseif ($request->get('paymentMethod') === self::CARD) {
            // Оплата по карте
            $response = $paymentService->buyTariff($data);
        }

        $userService->forgetTariffCache();

        return response()->api_success([
            'order' => $paymentService->order,
            'url' => $response->getConfirmation()->getConfirmationUrl(),
            'date' => $response->getCreatedAt(),
            'amount' => $response->getAmount(),
        ], 201);
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function getHistoryPayments(): JsonResponse
    {
        if (!$user = \auth()->user()) {
            throw new \Exception('Пользователь не найден');
        }

        $orders = Order::query()->where('user_id', $user->id)->get();

        return response()->api_success([
            'orders' => $orders,
        ], 201);
    }
}
