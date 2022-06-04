<?php

namespace Tests\Feature\Autoselect;

use App\Traits\UserForTest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RunAutoselectTest extends TestCase
{
    use UserForTest;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testPositive()
    {
        $randomDataFromDb = $this->prepareRandomRequestData();
        $response = $this->json('POST', config('app.url') . '/api/autoselect/run',
            array_merge(
                [
                    'api_token' => $this->getToken(),
                    'uuid'      => '81590d82-1f33-4ffc-8ce4-1ce47f5b8888',
                ],
                $randomDataFromDb
            )
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'autoselect_parameter_id',
                        'va_request_id',
                        'name',
                        'date',
                        'popularity',
                        'cart_add_count',
                        'avg_cost',
                        'crtc',
                        'category_id',
                        'category_popularity',
                        'category_cart_add_count',
                        'category_avg_cost',
                        'category_crtc',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'errors',
            ]);
    }

    public function testMissedKeywordValidation()
    {
        $randomDataFromDb = $this->prepareRandomRequestData();
        unset($randomDataFromDb['keyword']);
        $response = $this->json('POST', config('app.url') . '/api/autoselect/run',
            array_merge(
                [
                    'api_token' => $this->getToken(),
                    'uuid'      => '81590d82-1f33-4ffc-8ce4-1ce47f5b8888',
                ],
                $randomDataFromDb
            )
        );

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'data'   => [],
                'errors' => [
                    'keyword'
                ],
            ]);
    }

    public function testMissedcampaignProductIdValidation()
    {
        $randomDataFromDb = $this->prepareRandomRequestData();
        unset($randomDataFromDb['campaign_good_id']);
        $response = $this->json('POST', config('app.url') . '/api/autoselect/run',
            array_merge(
                [
                    'api_token' => $this->getToken(),
                    'uuid' => '81590d82-1f33-4ffc-8ce4-1ce47f5b8888',
                ],
                $randomDataFromDb
            )
        );

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'data'   => [],
                'errors' => [
                    'campaign_good_id'
                ],
            ]);
    }

    public function testMissedStartDateValidation()
    {
        $randomDataFromDb = $this->prepareRandomRequestData();
        unset($randomDataFromDb['start_date']);
        $response = $this->json('POST', config('app.url') . '/api/autoselect/run',
            array_merge(
                [
                    'api_token' => $this->getToken(),
                    'uuid'      => '81590d82-1f33-4ffc-8ce4-1ce47f5b8888',
                ],
                $randomDataFromDb
            )
        );

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'data'   => [],
                'errors' => [
                    'start_date'
                ],
            ]);
    }


    public function testMissedEndDateValidation()
    {
        $randomDataFromDb = $this->prepareRandomRequestData();
        unset($randomDataFromDb['end_date']);
        $response = $this->json('POST', config('app.url') . '/api/autoselect/run',
            array_merge(
                [
                    'api_token' => $this->getToken(),
                    'uuid'      => '81590d82-1f33-4ffc-8ce4-1ce47f5b8888',
                ],
                $randomDataFromDb
            )
        );

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'data'   => [],
                'errors' => [
                    'end_date'
                ],
            ]);
    }

    /**
     * @return array
     */
    protected function prepareRandomRequestData(): array
    {
        return array(
            'keyword'          => DB::table('keywords')->inRandomOrder()->limit(1)->value('name'),
            'campaign_good_id' => DB::table('campaign_goods')->inRandomOrder()->limit(1)->value('id'),
            'category_id'      => DB::table('categories')->inRandomOrder()->limit(1)->value('id'),
            'start_date'       => Carbon::today()->subDays(3),
            'end_date'         => Carbon::today()->subDays(2),
        );
    }
}
