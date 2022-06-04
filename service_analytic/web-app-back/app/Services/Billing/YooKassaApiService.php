<?php

namespace App\Services\Billing;

use App\Exceptions\YooKassa\YooKassaApiException;
use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use YooKassa\Client;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Request\Payments\CreatePaymentResponse;
use Illuminate\Support\Collection;

/**
 * Class YooKassaApiService
 * Сервис для отправки запросов в Yookassa
 * @package App\Services\Billing
 */
class YooKassaApiService
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Payment
     */
    public $payment;

    /**
     * YooKassaApiService constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->client = new Client();
        $this->client->setAuth(config('yookassa.shop_id'), config('yookassa.secret_key'));
        $this->user = $user;
    }

    /**
     * Отправка запроса с обработкой ошибок
     *
     * @throws YooKassaApiException
     */
    private function sendRequest($method, ...$data)
    {
        try {
            return call_user_func_array([$this->client, $method], $data);
        } catch (ApiException $exception) {
            throw new YooKassaApiException($exception->getCode());
        }
    }

    /**
     * Сгенерировать ключ идемпотентности
     *
     * @return string
     */
    public function generatetIdempotenceKey(): string
    {
        return uniqid('', true);
    }

    /**
     * Создать чек
     *
     * @param  \App\Models\Order $order
     * @return array
     */
    public function getReceipt($order): array
    {
        $related = ' ';
        if ($order->tariff) {
            $description =  $order->tariff->name . $related . 'период' . $related . $order->period;
        } else {
            $description =  "Оплата услуг" . $related . 'период' . $related . $order->period;
        }
        return [
            'items' => [
                [
                    'description' => $description,
                    'quantity' => 1,
                    'amount' => [
                        'value' => $order->amount,
                        'currency' => 'RUB',
                    ],
                    'vat_code' => 1,
                ]
            ],
            'email' => $this->user->email,
        ];
    }


    /**
     * Создание оплаты
     *
     * @param array $receipt
     * @param string $idempotenceKey
     * @param \App\Models\Order $order
     * @return false|mixed|CreatePaymentResponse
     * @throws YooKassaApiException
     */
    public function sendCreatePaymentRequest(array $receipt, string $idempotenceKey, Order $order)
    {
        $payment = [
            'amount' => [
                'value' => $order->amount,
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => config('yookassa.return_url') . "?payment_key=$idempotenceKey",
            ],
            'capture' => true,
            'description' => "SellerExpert. Оплата счета № " . $order->id . " на сумму " .  $order->amount. ' рублей',
            'receipt' => $receipt,
            'payment_method_data' => [
                'type' => 'bank_card'
            ]
        ];

        return $this->sendRequest('createPayment', $payment, $idempotenceKey);
    }
}
