<?php


namespace App\Services\V2;

use App\Exceptions\YooKassa\YooKassaApiException;
use App\Models\Payment;
use App\Models\User;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use YooKassa\Client;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Request\Payments\CreatePaymentResponse;
use Illuminate\Support\Collection;

/**
 * Class YooKassaApiService
 * Сервис для отправки запросов в Yookassa
 * @package App\Services\V2
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
     * @param  array $subscribe
     * @param Collection .$tariffs
     * @return array
     */
    public function getReceipt(array $subscribe,  Collection $tariffs): array
    {
        $related = ' ';

        foreach ($tariffs as $tariff)
        {
            $description =  $tariff->name . $related . 'период' . $related . $subscribe['period'];
            $data[] = [
                        'description' => $description,
                        'quantity' => 1,
                        'amount' => [
                            'value' => $subscribe['amount'],
                            'currency' => 'RUB',
                        ],
                        'vat_code' => 1,
                    ];
        }

        return [
            'items' => $data,
            'email' => $this->user->email,
        ];

    }

    /**
     * Создание оплаты
     *
     * @param array $receipt
     * @param string $idempotenceKey
     * @param array $subscribe
     * @return false|mixed|CreatePaymentResponse
     * @throws YooKassaApiException
     */
    public function sendCreatePaymentRequest( array $receipt, string $idempotenceKey, array  $subscribe)
    {
        $payment = [
            'amount' => [
                'value' => $subscribe['amount'],
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => config('yookassa.return_url') . "?payment_key=$idempotenceKey",
            ],
            'capture' => true,
            'description' => "SellerExpert. Оплата счета № " . $subscribe['order']->id . " на сумму " .  $subscribe['amount']. ' рублей',
            'receipt' => $receipt,
            'payment_method_data' => [
                'type' => 'bank_card'
            ]
        ];

        return $this->sendRequest('createPayment', $payment, $idempotenceKey);
    }
}
