<?php

namespace Tests\Feature;

use App\Helpers\StrategyHelper;
use App\Models\Status;
use App\Models\StrategyCpo;
use App\Traits\UserForTest;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class StrategyCpoCampaignHistoryExportTest extends TestCase
{
    use UserForTest;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $strategyCpo = StrategyCpo::whereHas('strategy', function (Builder $query) {
                $query->where('strategy_status_id', Status::ACTIVE)
                    ->whereNotNull('last_applying_date');
            })
            ->first();

        $response = $this->json('GET', config('app.url') . '/api/get-strategy-campaign-history-xls', [
            'api_token'  => $this->getToken(),
            'strategyId' => $strategyCpo->strategy_id
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'xlsLink',
                'xlsName'
            ],
            'success',
            'errors'
        ]);
    }
}
