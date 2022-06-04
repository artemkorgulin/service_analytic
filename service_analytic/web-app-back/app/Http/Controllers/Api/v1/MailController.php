<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MailRequest;
use App\Mail\OptimizationEmail;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    const MAILS_MAP = [
        'optimization' => [
            'subject' => 'mail.optimization.subject',
            'message' => 'mail.optimization.message',
        ],
        'advertising' => [
            'subject' => 'mail.advertising.subject',
            'message' => 'mail.advertising.message',
        ],
        'add_account' => [
            'subject' => 'mail.add_account.subject',
            'message' => 'mail.add_account.message',
        ],
        'need_help' => [
            'subject' => 'mail.need_help.subject',
            'message' => 'mail.need_help.message',
            'messageClass' => 'MessageHelp',
        ],
        'over10ksku' => [
            'subject' => 'mail.over10ksku.subject',
            'message' => 'mail.over10ksku.message',
            'messageClass' => 'MessageHelp',
        ],
    ];

    /**
     * Отправить заявку на оптимизацию товаров.
     *
     * @return mixed
     */
    public function send(MailRequest $request)
    {
        $type = $request->input('type');

        try {
            $user = \auth()->user();
            $data = $request->input('data');
            if (isset(self::MAILS_MAP[$type]['messageClass'])) {
                $messageClass = self::MAILS_MAP[$type]['messageClass'];
            } else {
                $messageClass = 'Message';
            }
            $messageClass = 'App\Http\Controllers\Api\v1\Mails\\'.$messageClass;
            $messageObject = new $messageClass();
            $messageParams = $messageObject->get($user, $data);
            $message = trans(self::MAILS_MAP[$type]['message'], $messageParams);
            $subject = trans(self::MAILS_MAP[$type]['subject']);

            Mail::send(new OptimizationEmail($message, $subject));

            return response()->api_success([]);
        } catch (Exception $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }
    }
}
