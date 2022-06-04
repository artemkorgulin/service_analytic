<?php

namespace App\Traits;

use App\Constants\RequestRepeatStatuses;
use App\Exceptions\Ozon\OzonApiException;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;
use Throwable;

trait GuzzleResponseHandler
{
    /**
     * Метод для обработки ответа от Guzzle
     *
     * @param $callback
     * @return array
     */
    private function formatResult($callback): array
    {
        try {
            $response = $callback();

            if (is_object($response) && method_exists($response, 'getBody')) {
                return [
                    'statusCode' => 200,
                    'data' => json_decode($response->getBody()->getContents(), true),
                ];
            }
            Log::channel('guzzle_errors')->error('Ошибка при получении данных по API: ' . print_r($response, true));

            return [
                'statusCode' => 400,
                'error' => __("Error in Ozon API service"),
            ];

        } catch (ConnectException | ClientException $exception) {
            Log::channel('guzzle_errors')->error('Ошибка при получении данных по API: ' . $exception->getMessage());

            return [
                'statusCode' => $exception->getCode() ?? 400,
                'error' => $exception->getMessage(),
            ];
        } catch (Exception $exception) {
            Log::channel('guzzle_errors')->error('Ошибка при получении данных по API: ' . $exception->getMessage());

            return [
                'statusCode' => 500,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Метод для отправки запросов в API с несколькими попытками
     * @TODO после рефакторинга появились проблемы с обработкой статусов ошибок надо это доделать ! SE-3431
     *
     * @param $method
     * @param mixed ...$params
     * @return array
     */
    public function repeat($method, ...$params): array
    {
        $count = 0;
        $result = [];

        do {
            try {
                $result = call_user_func_array([$this, $method], $params);
            } catch (Throwable $exception) {

                if ($count >= 3) {
                    throw new OzonApiException();
                }
            }
            $count++;
            if ($count > 1) {
                sleep(5);
            }
        } while (!empty($result['statusCode']) && (in_array($result['statusCode'],
                RequestRepeatStatuses::REPEAT_STATUSES) || $result['statusCode'] >= 500) && $count <= 3);

        return $result;
    }
}
