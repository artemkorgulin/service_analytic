<?php

namespace App\Classes\EventHandlers;

use App\Classes\MessageTemplater;
use App\Classes\Templater\Telegram;
use App\Events\NewNotification;
use App\Mail\NotificationEmail;
use App\Models\Notification;
use App\Models\NotificationSchema;
use App\Models\NotificationSendHistory;
use App\Models\NotificationSubtype;
use App\Models\NotificationTemplate;
use App\Models\NotificationUser;
use App\Models\User;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Constants\Notifications\WayCodes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class EventHandler
{

    const DEFAULT_LANG = 'ru';

    const EVENT_MAP = [
        'technical.system_update'                               => Goods::class,
        'goods.send_to_ozon'                                    => Goods::class,
        'hello.send_to_ozon'                                    => Hello::class,
        'billing.card_success'                                  => BillingBank::class,
        'billing.invoice_success'                               => BillingBank::class,
        'marketplace.account_product_upload_start'              => Marketplace::class,
        'marketplace.account_product_upload_success'            => MarketplaceSuccess::class,
        'marketplace.account_product_upload_fail'               => Marketplace::class,
        'card_product.marketplace_product_start_upload_success' => MarketplaceStartUpload::class,
        'card_product.marketplace_product_start_upload_fail'    => MarketplaceStartUpload::class,
        'technical.tarif_ended'                                 => TechicalGeneral::class,
        'technical.subscription_end_days'                       => SubscriptionDays::class,
        'marketplace.account_product_update_success'            => MarketplaceProductUpdateSuccess::class,
        'marketplace.wb_api_keys_failed'                        => MarketplaceApiTokenFailed::class,
        'marketplace.ozon_api_keys_failed'                      => MarketplaceApiTokenFailed::class,
        'company.add_user'                                      => CompanyAddUser::class,
        'card_product.product_problem'                          => ProductProblem::class,
        'private_office.company_corporate_end'                  => CompanyCorporateEnd::class,
    ];


    /**
     * @param  MessageTemplater  $messageTemplater
     */
    public function __construct(private MessageTemplater $messageTemplater)
    {
    }


    /**
     * @param  string  $eventCode
     * @param  array  $usersData
     * @param  array  $data
     */
    public function handle(string $eventCode, array $usersData, array $data): void
    {
        $notificationSubtype = NotificationSubtype::where('code', $eventCode)->firstOrFail();

        $firstNotificationTemplate = NotificationTemplate::where('subtype_id', $notificationSubtype->id)
            ->where('lang', static::DEFAULT_LANG)->firstOrFail();

        $classHandlerName = static::EVENT_MAP[$eventCode];
        $classHandler     = new $classHandlerName();
        $params           = $classHandler->getParams($usersData, $data['params']);

        foreach ($params as $param) {
            $notificationTemplate = $firstNotificationTemplate;

            if ($param['lang'] != static::DEFAULT_LANG) {
                $langNotificationTemplate = NotificationTemplate::where('subtype_id', $notificationSubtype->id)
                    ->where('lang', $param['lang'])->first();
                if ($langNotificationTemplate) {
                    $notificationTemplate = $langNotificationTemplate;
                }
            }

            $message      = $this->messageTemplater->getMessage($notificationTemplate->template, $param);
            $notification = Notification::create([
                'template_id' => $notificationTemplate->id,
                'message'     => $message,
                'type_id'     => $notificationSubtype->type_id,
                'subtype_id'  => $notificationSubtype->id,
                'created_at'  => Carbon::now(),
            ]);

            $usersBd = User::select('id', 'api_token', 'email', 'telegram_user_id')
                ->whereIn('id', $param['user_ids'])
                ->get()->keyBy('id');

            foreach ($usersBd as $user) {
                //todo: replace with single insert of multiple rows
                NotificationUser::create([
                    'user_id'         => $user->id,
                    'notification_id' => $notification->id,
                ]);

                //отправляем через сокеты
                NewNotification::dispatch($user->api_token, json_encode($notification));

                //находим настройки для отправки оповещения
                $notificationSchemas = NotificationSchema::ofUser($user)
                    ->ofType($notificationSubtype->type_id)
                    ->orderBy('created_at', 'DESC')->get();

                if ($notificationSchemas->isNotEmpty()) {
                    $subject = $this->getSubject($eventCode, $param);

                    $this->sendNotification($notification, $user, $message, $subject, $notificationSchemas);
                }
            }
        }
    }


    /**
     * @param  string  $eventCode
     * @param  mixed  $param
     *
     * @return mixed|string
     */
    private function getSubject(string $eventCode, mixed $param): mixed
    {
        $eventSubjects = trans('event_subjects');
        $subject       = $eventSubjects['default'];
        if (isset($eventSubjects[$eventCode])) {
            $subject = $eventSubjects[$eventCode];
            $subject = $this->messageTemplater->getMessage($subject, $param);
        }

        return $subject;
    }


    /**
     * Send notification
     *
     * @param  Notification  $notification
     * @param  User  $user
     * @param  string  $message
     * @param  mixed  $subject
     * @param  Collection  $notificationSchemas
     *
     * @return void
     */
    private function sendNotification(Notification $notification, User $user, string $message, mixed $subject, Collection $notificationSchemas): void
    {
        foreach ($notificationSchemas as $schema) {
            switch ($schema->way_code) {
                case WayCodes::EMAIL:
                    Mail::send(new NotificationEmail($user->email, $message, $subject));
                    break;
                case WayCodes::TELEGRAM:
                    app('telegram.bot')->sendMessage([
                        'text' => $this->messageTemplater->htmlToMessage(new Telegram(), $message),
                        'chat_id' => $user->telegram_user_id,
                    ]);
                    break;
            };
            NotificationSendHistory::create([
                'notification_id' => $notification->id,
                'user_id'         => $user->id,
                'way_code'        => $schema->way_code,
                'created_at'      => Carbon::now(),
            ]);
        }
    }
}
