<?php

namespace App\Services\Bots\Telegram\Callbacks;

use App\Services\Bots\Telegram\CallbackInterface;
use App\Services\Bots\Telegram\Callbacks;
use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Traits\Singleton;

class CallbackQueryBus extends \Telegram\Bot\Answers\AnswerBus
{

    use Singleton;

    /**
     * @var array
     */
    protected $callbacks = [];

    /**
     * @var array
     */
    private $dataClasses = [];

    /**
     * @var BotsManager
     */
    private BotsManager $manager;


    /**
     * Returns the singleton instance of this class.
     *
     * @return static The Singleton instance.
     */
    public static function Instance(...$arguments)
    {
        if (null === self::$instance) {
            self::$instance = new static(...$arguments);
        }

        return self::$instance;
    }


    /**
     * Instantiate CallbackQuery Bus
     *
     * @param  Api|null  $telegram
     * @param  BotsManager  $manager
     */
    public function __construct(?Api $telegram, BotsManager $manager)
    {
        $this->telegram = $telegram;
        $this->manager  = $manager;

        $this->parseCallbacks();
    }


    /**
     * @param  Update  $update
     *
     * @return void
     */
    public function handler(Update $update)
    {
        $callbackQueryRequest = Callbacks\CallbackQueryRequest::make($update->callbackQuery->data);

        $this->executeCallback($callbackQueryRequest, $update);
    }


    /**
     * @return Api
     */
    public function getTelegram(): Api
    {
        return $this->telegram;
    }


    /**
     * @param  Api|null  $telegram
     *
     * @return CallbackQueryBus
     */
    public function setTelegram(?Api $telegram): CallbackQueryBus
    {
        $this->telegram = $telegram;

        return $this;
    }


    /**
     * @return BotsManager
     */
    public function getManager(): BotsManager
    {
        return $this->manager;
    }


    /**
     * @param  BotsManager  $manager
     *
     * @return CallbackQueryBus
     */
    public function setManager(BotsManager $manager): CallbackQueryBus
    {
        $this->manager = $manager;

        return $this;
    }


    /**
     * @return array
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }


    /**
     * @param  iterable  $callbacks
     *
     * @return $this
     */
    public function addCallbacks(iterable $callbacks): self
    {
        foreach ($callbacks as $callback => $callbackRequest) {
            $this->addCallback($callback, $callbackRequest);
        }

        return $this;
    }


    /**
     * @param  string  $callback
     * @param  string  $callbackRequest
     *
     * @return $this
     * @throws TelegramSDKException
     */
    public function addCallback($callback, $callbackRequest): self
    {
        $callback = $this->resolveCallback($callback);

        $methodName                     = $callback->getMethod();
        $this->callbacks[$methodName]   = $callback;
        $this->dataClasses[$methodName] = $callbackRequest;

        return $this;
    }


    /**
     * @param  object|string  $callback
     *
     * @return CallbackInterface
     * @throws TelegramSDKException
     */
    private function resolveCallback($callback)
    {
        $callback = $this->makeCallbackObj($callback);

        if (!($callback instanceof CallbackInterface)) {
            throw new TelegramSDKException(
                sprintf(
                    'Command class "%s" should be an instance of "%s"',
                    get_class($callback),
                    CallbackInterface::class
                )
            );
        }

        return $callback;
    }


    /**
     * @param $name
     *
     * @return $this
     */
    public function removeCallback($name): self
    {
        unset($this->callbacks[$name]);

        return $this;
    }


    /**
     * @param  string  $method
     * @param  mixed  $data
     * @param  Update  $update
     *
     * @return bool
     */
    private function executeCallback(CallbackQueryRequest $callbackQueryRequest, Update $update): mixed
    {
        $method = $callbackQueryRequest->method;
        $data   = $this->resolveData($method, $callbackQueryRequest->data);

        /** @var Callback $callback */
        $callback = $this->callbacks[$method] ??
            collect($this->callbacks)->filter(function ($callback) use ($method) {
                return $callback instanceof $method;
            })->first() ?? null;

        return $callback ? $callback->make($this->telegram, $data, $update) : false;
    }


    /**
     * @param $callback
     *
     * @return mixed|object
     * @throws TelegramSDKException
     */
    private function makeCallbackObj($callback)
    {
        if (is_object($callback)) {
            return $callback;
        }

        if (!class_exists($callback)) {
            throw new TelegramSDKException(
                sprintf(
                    'Callback class "%s" not found! Please make sure the class exists.',
                    $callback
                )
            );
        }

        if ($this->telegram->hasContainer()) {
            return $this->buildDependencyInjectedAnswer($callback);
        }

        return new $callback();
    }


    /**
     * @return void
     */
    private function parseCallbacks()
    {
        $callbacks = $this->manager->getConfig('callbacks', []);
        $this->addCallbacks($callbacks);
    }


    /**
     * @param  string  $method
     * @param $data
     *
     * @return mixed
     */
    private function resolveData(string $method, $data): mixed
    {
        if (!empty($this->dataClasses[$method])) {
            $data = new $this->dataClasses[$method]($data);
        }

        return $data;
    }
}
