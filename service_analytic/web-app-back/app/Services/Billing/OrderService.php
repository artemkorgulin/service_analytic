<?php

namespace App\Services\Billing;

use App\Helpers\PaymentHelper;
use Carbon\Carbon;

use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;

use App\Http\Requests\Api\BillingOrderRequest;

use App\Services\Billing\PaymentService;
use App\Services\CalculateService;
use App\Services\CompanyService;

use App\Repositories\CompanyRepository;

use App\Models\Company;
use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\Tariff;

/**
 * Сервис для работы с заказами в биллинге 2022 года
 */
class OrderService
{
    public const BANK = 'bank';
    public const CARD = 'card';

    /**
     * Задать тариф заказу и закрепить с ним услуги этого тарифа
     */
    public function setupTariffForOrder(Order $order, Tariff $tariff)
    {
        $order->tariff_id = $tariff->id;
    }

    /**
     * Закрепить набор услуг за заказом
     */
    public function addServicesToOrder(Order $order, array $services)
    {
        foreach ($services as $serviceData) {
            $service = Service::find($serviceData['id']);
            $order->services()->attach(
                $service->id,
                [
                    'ordered_amount' => $serviceData['amount'],
                    'total_price' => $service->humanTotalPriceFor($serviceData['amount'])
                ]
            );
        }
    }

    /**
     * Если у пользователя есть промокод, то закрепить его за данным заказом
     */
    public function addPromocodeFromUser(Order $order, $user)
    {
        $userPromocode = $user->haveUnusedDiscountPromocode() ?
            $user->nextDiscountPromocode() : null;

        if ($userPromocode) {
            $userPromocode->makeUsedForOrder($order);
        }
    }

    /**
     * Сделать заказ активным
     */
    public function activateOrder(Order $order)
    {
        $now = Carbon::now();
        $order->start_at = Carbon::now();
        $order->end_at = $now->add(30 * $order->period, 'days');
    }


    /**
     * Получить текущий тариф пользователя
     */
    public function currentUserTariff(User $user): ?array
    {
        $order = $user->directOrders()->active()->whereNotNull('tariff_id')->latest()->first();
        if ($order) {
            return [
                'id' => $order->tariff->id,
                'name' => $order->tariff->name,
            ];
        }
        return null;
    }

    /**
     * Активен
     */
    public function isFreeTariff(User $user)
    {
        if ($this->currentUserTariff($user)) {
            return false;
        }
        return true;
    }

    /**
     * Получить активные услуги для данного пользователя.
     *
     * @param  User|Company  $model
     * @return array
     */
    public function activeServices(User|Company $model): array
    {
        $orders = $model->directOrders()->active()->with('services')->get();
        $services = [];
        foreach ($orders as $order) {
            foreach ($order->services as $service) {
                if (!isset($services[$service->id])) {
                    $services[$service->id] = [
                        "id" => $service->id,
                        "name" => $service->name,
                        'alias' => $service->alias,
                        'countable' => $service->countable,
                        "description" => $service->description,
                        "amount" => 0,
                        "periods" => []
                    ];
                }

                $services[$service->id]['periods'][] = [
                    'start_at' => $order->start_at,
                    'ordered' => $service->pivot->ordered_amount,
                    'end_at' => $order->end_at,
                ];

                $services[$service->id]['amount'] += $service->pivot->ordered_amount;
            }
        }
        return array_values($services);
    }

    /**
     * Создать заказ для пришедшего запроса по http
     */
    public function createOrderForRequest(BillingOrderRequest $request)
    {
        $orderService = $this;
        return ModelHelper::transaction(
            function () use ($request, $orderService) {
                $order = (new Order())->saveOrder(
                    [
                        'currency' => Order::CURRENCY_RUB,
                        'amount' => (new CalculateService)
                            ->finalPriceForBillingOrderRequest($request),
                        'status' => Order::PENDING,
                        'period' => $request->duration,
                        'user_id' => auth()->user()->id,
                    ]
                );

                if ($request->tariff_id) {
                    $orderService->setupTariffForOrder($order, $request->tariff());
                }

                if ($request->services) {
                    $orderService->addServicesToOrder($order, $request->services);
                }

                $orderService->addPromocodeFromUser($order, auth()->user());

                $paymentService = new PaymentService(auth()->user());
                $orderResponse = [];

                // Если в запросе есть компания и есть корпоративный доступ - значит за ней закрепляем заказ
                if (PaymentHelper::serviceIsset($request->services, 'corp')) {
                    $orderService->addCompanyFromRequest($request, $order);
                }

                if ($request->paymentMethod === self::BANK) {
                    // Оплата по счету
                    $paymentService->generateOrderInBank($order);

                    $orderResponse = [ 'orderId' => $order->id ];
                } elseif ($request->paymentMethod === self::CARD) {
                    $response = $paymentService->buyTariff($order);

                    $orderResponse = [
                            'order' => $order,
                            'url' => $response
                                ->getConfirmation()
                                ->getConfirmationUrl(),
                            'date' => $response->getCreatedAt(),
                            'amount' => $response->getAmount(),
                        ];
                }

                $order->save();
                return $orderResponse;
            }
        );
    }

    /**
     * Добавить компанию к заказу из запроса
     */
    private function addCompanyFromRequest(BillingOrderRequest $request, Order $order)
    {
        if ($request->company or $request->company_id) {
            $order->company_id = $this->getCompany($request)->id;
        }
    }

    /**
     * Получить компанию для внутренней обработки из запроса
     */
    private function getCompany(BillingOrderRequest $request)
    {
        if ($request->company_id) {
            return (new CompanyRepository)->find($request->company_id);
        }

        if ($request->company) {
            $company = Company::where('inn', $request->company['inn'])->first();

            if (!$company) {
                $companyService = new CompanyService;
                $company = $companyService->createCompany($request->company);
                $companyService->adoptCompany($company, auth()->user());
            }

            return $company;
        }

        throw new \Exception('Нет данных для компании в запросе для заданного типа');
    }

    /*
     * @param User|Company $model
     * @param string $alias
     * @return bool
     */
    public function hasService(User|Company $model, string $alias): bool
    {
        $activeServices = $this->activeServices($model);

        foreach ($activeServices as $activeService) {
            if ($activeService['alias'] === $alias) {
                return true;
            }
        }

        return false;
    }
}
