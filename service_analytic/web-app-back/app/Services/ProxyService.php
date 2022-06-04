<?php

namespace App\Services;

use Auth;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Http;
use Log;

/**
 * Class ProxyService
 * Сервис для проксирования запросов на другие сервера.
 * Для сервера должен быть создан конфиг в папке config.
 * Название этого файла передаётся в параметр $configName при создании класса.
 * В файле должны быть минимум ключи url_v1 и token
 *
 * @package App\Services
 */
class ProxyService
{
    private $configName;
    private $configKey;
    private $method;
    private $request;
    private $url;
    private $errorMessage = 'Ошибка выполнения запроса к внешнему сервису.';
    private $downLoadedContentTypes = [
        'application/pdf', 'application/zip', 'application/gzip', 'application/msword',
        'image/jpeg', 'image/gif', 'image/jpeg'
    ];


    /**
     * Get proxy service config by uri
     *
     * @param string $uri
     *
     * @return string
     */
    public static function getConfigByUri($uri): string
    {
        foreach (config('api_servers_data.config_files') as $key => $value) {
            if(strpos($uri, $key) !== false) {
                return $value;
            }
        }

        return '';
    }


    /**
     * ProxyService constructor.
     *
     * @param  string  $configName  server name file config
     * @param  null  $version  API version
     * @param  string|null  $name  request params
     * @param  Authenticatable|null  $user  User that'll be used in request
     */
    public function __construct(string $configName, $version = null, $name = null, $user = null)
    {
        $this->configName = $configName;
        $this->method = strtolower(request()->method());
        $this->request = request();
        $this->configKey = config(sprintf('%s.url%s', $this->configName, !empty($version) ? '_'.$version : ''));
        $this->url = sprintf('%s/%s', $this->configKey, $name);
        $this->user = $user ?? Auth::user();
    }


    /**
     * Set request object
     *
     * @param $request
     *
     * @return ProxyService
     */
    public function setRequest($request): self
    {
        $this->request = $request;
        return $this;
    }


    /**
     * Set request method
     *
     * @param  string  $method
     *
     * @return ProxyService
     */
    public function setMethod(string $method): self
    {
        $this->method = strtolower($method);
        return $this;
    }

    /**
     * Execute request
     *
     * @return mixed
     */
    public function handle()
    {
        // Validate
        if (empty($this->configKey) || empty($this->request) || empty($this->user) || strlen($this->url) < 10) {
            Log::error(sprintf(
                'Переданы пустые значения при обращении к классу %s. Обращение к другому API сервису невозможно.',
                self::class
            ));
            return response()->api_fail($this->errorMessage, [], 422);
        }

        $userService = new UserService($this->user);

        try {
            $tariff = $userService->setTariffCache();
            if (!$tariff) {
                $tariff = false;
            } else {
                $tariff = $tariff['tariff'][0];
                unset($tariff['description']);
            }

            $this->request->request->add([
                'user' => $userService->setUserDataCache(),
                'account' => $userService->setAccountCache(),
                'permissions' => $userService->setPermissionsCache(),
                'tariff' => $tariff,
                'max_sku_count' => $tariff['sku'] ?? 0,
                'max_escrow_count' => $tariff['escrow'] ?? 0,
                'company' => auth()->user()->getSelectedCompany()?->only('id', 'name') ?? false,
                'ip' => \Request::ip()
            ]);

            Log::info('Запрос к внешнему сервису', $this->request->all());

            $response = Http::withHeaders([
                'Authorization-Web-App' => config($this->configName.'.token'),
            ])->{$this->method}($this->url, $this->request->all());

            // Вот здесь добавлена обработка ТОЛЬКО для того чтобы можно было скачивать файлы возвращаемые
            // приложениями если контент type соответствует одному из downLoadedContentTypes
            // СКАЧИВАЕМ
            $contentType = $response->header('Content-Type');
            if (in_array($contentType, $this->downLoadedContentTypes)) {
                $headers = $response->headers();
                return \Response::make($response->getBody(), $response->status(), $headers);
            }

            // ответ
            if ($response->status() > 320) {
                Log::error(sprintf(
                    "Ошибка при обращении к другому API сервису. Статус ответа %s\nТело ответа: %s\n",
                    $response->status(),
                    $response->getBody()
                ));

                if ($response->json() && (!empty($response->json()))) {
                    return response($response->json(), $response->status());
                } else {
                    return response()->api_fail('Ошибка выполнения запроса к внешнему сервису',
                        [$response->getBody()],
                        $response->status());
                }
            }

            // Это обычный ответ json или html
            return $response;
        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

        return response()->api_fail('Ошибка выполнения запроса к внешнему сервису');
    }
}
