<?php

namespace Tests\Api\v2;

use App\Models\Tariff;
use AnalyticPlatform\LaravelHelpers\Tests\TestHttp;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * @property TestHttp $http
 */
class TariffTest extends TestCase
{
    const TARIFF_INDEX_URL = '/v2/billing/tariffs';
    const TARIFF_SHOW_URL = '/v2/billing/tariffs/';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->http = new TestHttp();
    }

    /**
     * @return void
     */
    public function testTariffIndex(): void
    {
        $getTariffs = Tariff::all();

        $this->http->get(self::TARIFF_INDEX_URL);

        $this->http->assertStatus(Response::HTTP_OK);

        $responseArray = $this->http->json();

        $this->assertEquals($getTariffs->count(), count($responseArray['data']));
        $this->assertEquals([], $responseArray['errors']);

        foreach ($getTariffs as $tariff) {
            $searchTariffKey = array_search($tariff->id, array_column($responseArray['data'], 'id'));

            $this->assertNotFalse($searchTariffKey);
            $this->assertEquals($tariff->services()->count(), count($responseArray['data'][$searchTariffKey]['services']));
        }
    }

    /**
     * @return void
     */
    public function testTariffIndexStructure(): void
    {
        $this->http->get(self::TARIFF_INDEX_URL);

        $this->http->assertStructure([
            'errors',
            'success',
            'data' => [
                '*' => [
                    'alias',
                    'description',
                    'id',
                    'name',
                    'price',
                    'services' => [
                        '*' => [
                            'alias',
                            'amount',
                            'countable',
                            'description',
                            'id',
                            'max_order_amount',
                            'min_order_amount',
                            'name',
                            'sellable',
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testTariffShow(): void
    {
        $tariffId = Tariff::inRandomOrder()->first()->id;
        $this->http->get($this->getTariffShowPageUrl($tariffId));

        $this->http->assertStatus(Response::HTTP_OK);

        $responseArray = $this->http->json();

        $this->assertEquals($tariffId, $responseArray['data']['id']);
        $this->assertEquals([], $responseArray['errors']);

        $this->http->assertStructure([
            'success',
            'errors',
            'data' => [
                'id',
                'name',
                'alias',
                'description',
                'price',
                'services' => [
                    '*' => [
                        'id',
                        'name',
                        'alias',
                        'min_order_amount',
                        'max_order_amount',
                        'countable',
                        'sellable',
                        'description',
                        'amount',
                    ]
                ]
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testTariffShowErrorNotExistId(): void
    {
        $tariffId = Tariff::query()->orderBy('id', 'desc')->first()->id + 1;
        $this->http->get($this->getTariffShowPageUrl($tariffId));


        $this->http->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->http->assertStructure([
            'error' => [
                'http_code',
                'code',
                'message',
                'advanced' => [
                    ['id']
                ]
            ]
        ]);

        $responseArray = $this->http->json();

        $this->assertEquals('Выбранное значение для id некорректно.', $responseArray['error']['advanced'][0]['id']);
    }

    /**
     * @param int $tariffId
     * @return string
     */
    public function getTariffShowPageUrl(int $tariffId)
    {
        return self::TARIFF_SHOW_URL.$tariffId;
    }
}
