<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Services\Bots\Telegram\Callbacks\CallbackQueriesHandler;
use App\Services\Bots\Telegram\Members\MemberHandler;
use Psr\Http\Message\ServerRequestInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class TelegramBotController extends Controller
{

    public function handleRequest(ServerRequestInterface $request)
    {
        /** @var Api $api */
        $api    = \Telegram::bot();

        /** @var Update $update */
        $update = $api->commandsHandler(true, $request);

        //todo: add handlers to Api using macroable
        (new CallbackQueriesHandler($api, app('telegram')))->handle($update);
        (new MemberHandler($api, app('telegram')))->handle($update);

        return 'ok';
    }


    public function getBotStartLink()
    {
        $userId = request()->input('user.id');
        /** @var User $user */
        $user = User::findOrFail($userId);

        $result = [
            'url' => sprintf(
                'https://t.me/%s?start=%s',
                config('telegram.bots.default_bot.username'),
                $user->getOrGenerateVerificationToken()
            )
        ];

        return response()->api_success($result);
    }
}
