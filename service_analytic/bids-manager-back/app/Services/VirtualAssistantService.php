<?php


namespace App\Services;

use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\Collection;

class VirtualAssistantService
{
    protected static array $errors = [];

    /**
     * VirtualAssistantService connector
     *
     * @return VirtualAssistantService
     */
    public static function connect()
    {
        return new static();
    }

    /**
     * Получить последнюю ошибку
     * @return mixed
     */
    public static function getLastError()
    {
        return array_pop(static::$errors);
    }

    /**
     * Получить популярность ключевого слова
     *
     * @param Collection $keywords
     * @param string     $dateFrom
     * @param string     $dateTo
     * @return bool|object
     */
    public function getKeywordsPopularity($keywords, $dateFrom, $dateTo)
    {
        $params = [
            'keywords' => $keywords->toArray(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];
        $res = $this->request('post', '/get-keywords-popularity', $params);
        return $res ? ($res->success ? $res->data : null) : false;
    }

    /**
     * Запрос к ВП
     *
     * @param string $method
     * @param string $uri
     * @param array  $params
     * @return bool|object
     */
    protected function request($method, $uri, $params = null)
    {
        $client = new GuzzleHttp\Client([
            'base_uri' => config('virtual_assistant.api_url'),
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ]
        ]);

        $uri = config('virtual_assistant.api_url').$uri;

        $options = [];
        if( $params ) {
            $options[RequestOptions::JSON] = $params;
        }

        try {
            $res = $client->request($method, $uri, $options);
        }
        catch (GuzzleException $e) {
            static::$errors[] = [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'method'  => $method,
                'uri'     => $uri,
                'request' => $options,
            ];
            return false;
        }

        $answer = $res->getBody();
        $result = json_decode($answer);

        if( $res->getStatusCode() == 200 ) {
            return $result;
        }

        $result = json_decode($answer);
        static::$errors[] = [
            'code'    => $result->error->code,
            'message' => $result->error->message,
            'method'  => $method,
            'uri'     => $uri,
            'request' => $options,
        ];

        return false;
    }


    /**
     * Поиск статистики по ключевому слову
     *
     * @param $params
     * @return false|mixed
     */
    public function findKeywordsWithStatistic($params)
    {
        $res = $this->request('post', '/find-keywords-with-statistics', $params);
        return $res ? ($res->success ? $res->data : null) : false;
    }
}
