<?php

Namespace App\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Trait GuzzleResponseHandler
 * Сервис с вспомогательными методами для отправки апи запросов
 * @package App\Traits
 */
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

            return [
                'statusCode' => 200,
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        } catch (\GuzzleHttp\Exception\ConnectException | \GuzzleHttp\Exception\ClientException $exception) {
            report($exception);
            Log::channel('guzzle_errors')->error('Ошибка при получении данных по API: ' . $exception->getMessage());
            return [
                'statusCode' => $exception->getResponse()->getStatusCode(),
                'error' => $exception->getMessage(),
            ];
        } catch (\Exception $exception) {
            report($exception);
            Log::channel('guzzle_errors')->error('Ошибка при получении данных по API: ' . $exception->getMessage());
            return [
                'statusCode' => 500,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Метод для повторной отправки запросов в API
     *
     * @param $method
     * @param mixed ...$params
     * @return array
     */
    public function repeat($method, ...$params): array
    {
        $count = 0;
        do {
            $result = call_user_func_array([$this, $method], $params);
            $count++;
            if ($count > 1) {
                sleep(5);
            }

        } while($result['statusCode'] !== 200 && $count <= 3);

        return $result;
    }
}
