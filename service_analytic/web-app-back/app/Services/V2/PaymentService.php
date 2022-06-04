<?php


namespace App\Services\V2;


use App\Exceptions\YooKassa\YooKassaApiException;
use App\Models\AbolitionReason;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentRecipient;
use App\Models\TariffActivity;
use App\Models\User;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use YooKassa\Model\PaymentInterface;
use YooKassa\Model\PaymentStatus;
use YooKassa\Request\Payments\CreatePaymentResponse;

/**
 * Class PaymentService
 * Сервис работы с платежами
 * @package App\Services\V2
 */
class PaymentService
{
    const TEST_USER = 15;

    /**
     * @var Order|null
     */
    public ?Order $order;

    /**
     * @var TariffActivity|null
     */
    public ?TariffActivity $tariffActivity;

    /**
     * @var User
     */
    protected User $user;

    /**
     * PaymentService constructor.
     * @param Order|null $order
     * @param TariffActivity|null $tariffActivity
     */
    public function __construct(User $user, Order $order = null, $tariffActivity = null)
    {
        $this->user = $user;
        $this->order = $order;
        $this->tariffActivity = $tariffActivity;
    }

    /**
     * Создание оплаты
     *
     * @param array $subscribe
     * @return CreatePaymentResponse
     * @throws YooKassaApiException
     */
    public function buyTariff(array $subscribe): CreatePaymentResponse
    {
        $yookassaService = new YooKassaApiService($this->user);
        $receipt = $yookassaService->getReceipt($subscribe, $subscribe['tariffsCollection']);
        $idempotenceKey = $yookassaService->generatetIdempotenceKey();
        $response = $yookassaService->sendCreatePaymentRequest($receipt, $idempotenceKey, $subscribe);
        $this->createOrderFromResponse($response, $subscribe['tariff_id'], $receipt, $idempotenceKey, $subscribe);

        return $response;
    }

    /**
     * Создать ордер
     *
     * @param  CreatePaymentResponse  $response
     * @param  array  $tariffs
     * @param  array  $receipt
     * @param  string  $idempotenceKey
     * @param  array  $subscribe
     */
    private function createOrderFromResponse(CreatePaymentResponse $response, array $tariffs,
        array $receipt, string $idempotenceKey, array $subscribe)
    {
        $recipient = new PaymentRecipient();
        $recipient->account_id = $response->getRecipient()->account_id;
        $recipient->gateway_id = $response->getRecipient()->gateway_id;
        $recipient->save();
        $this->order = $subscribe['order'];
        $this->order->user_id = $this->user->id;
        $this->order->status = $response->getStatus();
        $this->order->idempotence_key = $idempotenceKey;
        $this->order->receipt =collect($receipt)->toJson(JSON_UNESCAPED_UNICODE);
        $this->order->amount =  $response->getAmount()->value;
        $this->order->currency =  $response->getAmount()->currency;
        $this->order->description = $response->getDescription();
        $this->order->yookassa_id = $response->getId();
        $this->order->created_at = $response->getCreatedAt();
        $this->order->paid = $response->getPaid();
        $this->order->test = $response->getTest() ?? false;
        $this->order->refundable = $response->getRefundable();
        $this->order->transfers = $response->getTransfers();
        $this->order->receipt = $receipt;
        $this->order->receipt_registration = $response->getReceiptRegistration() ?? 'pending';
        $this->order->save();
    }

    /**
     * Создать ордер для оплаты по счету
     *
     * @param array $subscribe
     *
     * @return Order
     */
    public function generateOrderInBank(array $subscribe ): Order
    {
        $yooKassaService = new YooKassaApiService($this->user);
        $receipt = $yooKassaService->getReceipt($subscribe,   $subscribe['tariffsCollection']);
        $idempotenceKey = $yooKassaService->generatetIdempotenceKey();

        $this->order = $subscribe['order'];
        $this->order->user_id = $this->user->id;
        $this->order->idempotence_key = $idempotenceKey;
        $this->order->receipt =collect($receipt)->toJson(JSON_UNESCAPED_UNICODE);
        $this->order->description = "SellerExpert. Оплата счета № " .  $subscribe['order']->id . " на сумму " . $subscribe['order']->amount . ' рублей';
        $this->order->receipt = $receipt;
        $this->order->type = 'bank';
        $this->order->save();

        return $this->order;
    }

    /**
     * Обновление ордера из хука
     *
     * @param CreatePaymentResponse $interface
     */
    public function updateOrder(PaymentInterface $interface): void
    {
        Log::info('updateOrder    ' . serialize(\request()->json()));

        $this->order->captured_at = $interface->getCapturedAt();
        $this->order->status = $interface->getStatus();
        $this->order->paid = $interface->getPaid();
        $this->order->income_amount = $interface->getIncomeAmount()->value ?? null;
        $this->order->test = $interface->getTest() ?? false;
        $this->order->refundable = $interface->getRefundable();
        $this->order->captured_at = $interface->getCapturedAt();
        $details = $interface->getCancellationDetails();
        if ($details) {
            $reason = new AbolitionReason();
            $details = $interface->getCancellationDetails();
            $reason->party = $details->party;
            $reason->reason = $details->reason;
            $reason->save();
            $this->order->abolitionReason()->associate($reason);
        }
        $paymentMethod = $interface->getPaymentMethod();
        if ($paymentMethod) {
            if ($this->order->payment_method_id) {
                $paymentMethodModel = $this->order->paymentMethod;
            } else {
                $paymentMethodModel = new PaymentMethod();
            }
            $paymentMethodModel->type = $paymentMethod->getType();
            $paymentMethodModel->yookassa_id = $paymentMethod->getId();
            $paymentMethodModel->saved = $paymentMethod->getSaved();
            $paymentMethodModel->title = $paymentMethod->getTitle();
            $paymentMethodModel->save();
            $this->order->paymentMethod()->associate($paymentMethodModel);
        }
        $this->order->save();
    }

    /**
     * Обновление подписки
     */
    public function updateOrderInBank(): void
    {
        $this->order->status = PaymentStatus::SUCCEEDED;
        $this->order->captured_at = now();

        $this->order->save();

        Cache::forget('proxy_user_tariffs_'.$this->user->id);

        (new TariffActivityService($this->order))->activateSubscription();

        Log::channel('yokassa_hooks')->info('billing.bank_success',
            ['orderId' => $this->order->id, 'userId' => $this->order->user_id, 'anount' => $this->order->amount]);

        UsersNotification::dispatch(
            'billing.invoice_success',
            [
                ['id' => $this->order->user_id, 'fio' => $this->user->name, 'email' => $this->user->email,  'lang' => 'ru'],
                ['id' => self::TEST_USER, 'fio' => $this->user->name, 'email' => 's.garbusha@yandex.ru', 'lang' => 'ru'],
            ],
            ['order' => [['title' => 'Успешная оплата пакета на сумму', 'price' => $this->order->amount]]]
        );
    }

    /**
     * @param  Order  $order
     * @return array
     */
    public function getSubscription(Order $order): array
    {
        $tariffActivity = TariffActivity::query()->where('order_id', '=', $order)->get();

        return $tariffActivity->toArray();
    }
}
