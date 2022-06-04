<?php

namespace App\Services\V2;

use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MPStatsApi
{
    public const FIELD_STATUS_CODE = 'statusCode';
    public const FILED_DATA = 'data';
    public const FIELD_MESSAGE = 'message';
    public const FIELD_START_DATE = 'd1';
    public const FIELD_STOP_DATE = 'd2';

    public const LOG_CHANNEL = 'guzzle_request';
    public const STATUS_CODE_NOT_RESPONSE = 0;
    public const STATUS_TEXT_NOT_RESPONSE = 'Нет данных';

    private string $host;
    private array $headers;

    public function __construct()
    {
        $this->host = config('env.mpstats_host_url');
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Mpstats-TOKEN' => config('env.mpstats_token')
        ];
    }

    /**
     * Получить статистику по товару
     *
     * @param int $sku
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @return array
     */
    public function getProductStats(int $sku, DateTime $dateStart, DateTime $dateEnd): array
    {
        return $this->fetch('oz/get/item/' . $sku . '/by_category', [
            self::FIELD_START_DATE => $dateStart->format('Y-m-d'),
            self::FIELD_STOP_DATE => $dateEnd->format('Y-m-d'),
        ]);
    }

    /**
     * Fetch a data in MPStatsApi
     *
     * @param string $url
     * @param array $params
     * @return array
     */
    private function fetch(string $url, array $params): array
    {
        $result = [
            self::FIELD_STATUS_CODE => self::STATUS_CODE_NOT_RESPONSE,
            self::FILED_DATA => self::STATUS_TEXT_NOT_RESPONSE,
        ];

        try {
            $url = $this->host . $url;
            $response = Http::withHeaders($this->headers)->get($url, $params);
            $result[self::FIELD_STATUS_CODE] = $response->status();

            if ($response->failed()) {
                $result[self::FILED_DATA] = $response->json()[self::FIELD_MESSAGE] ?? '';
                $this->handlerError($url, $response->body());

                return $result;
            }

            $result[self::FILED_DATA] = $response->json();
            return $result;
        } catch (\Exception $exception) {
            $this->handlerError($url, $exception->getMessage());
        }

        return $result;
    }

    /**
     * Handler error for response MPStatsApi
     *
     * @param string|null $url
     * @param string|null $message
     * @return void
     */
    private function handlerError(?string $url, ?string $message): void
    {
        if (config('app.env') !== 'testing') {
            Log::channel(self::LOG_CHANNEL)->error("MPStatsApi failed request to '$url'\nMessage: '$message'");
        }
    }
}
