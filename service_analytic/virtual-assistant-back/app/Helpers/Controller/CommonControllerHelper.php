<?php

namespace App\Helpers\Controller;

use App\Services\Wildberries\Client;
use Illuminate\Http\Request;

class CommonControllerHelper
{
    /**
     * Для хранения пользователя
     *
     * @var
     */
    public $user;

    /**
     * Для хранения аккаунта
     *
     * @var
     */
    public $account;

    /**
     * Для хранения клиента
     * @var
     */
    public $client;

    public string $myPlatformTitle = 'Wildberries';

    public $pagination = 15;

    /**
     * Contructor
     * @param Request $request
     * @throws \Exception
     */
    public function __construct(Request $request)
    {
        // сеттим пользователя
        if ($request->user) {
            $this->user = $request->user;
        }

        // сеттим его аккаут по wb
        if (isset($request->account)) {
            $this->account = $request->account;
        }

        // и тут же пытаемся создать клиента
        if ($this->account) {
            $this->client = new Client($this->account['platform_client_id'], $this->account['platform_api_key']);
        }
    }
}
