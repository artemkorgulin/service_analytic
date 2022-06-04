<?php

namespace Tests\Services\Loaders;

use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Services\Loaders\OzonCampaignsLoader;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\TestsGuzzleRequests;
use Tests\Traits\TestsInvisibleMethods;

class OzonCampaignsLoaderTest extends TestCase
{

    use TestsInvisibleMethods, TestsGuzzleRequests;

    public function testStart()
    {
        $loader = new OzonCampaignsLoader();
        $loader->setClient($this->getClient());

        $body          = file_get_contents('./tests/Env/response.json');
        $responseCheck = json_decode($body, true);
        $ids           = Arr::pluck($responseCheck['list'], 'id');

        $idsToDelete = array_slice($ids, 0, ceil(count($ids) / 2));
        CampaignProduct::query()->whereIn('campaign_id', $idsToDelete)->delete();
        Campaign::query()->whereIn('id', $idsToDelete)->delete();

        $countBefore = Campaign::whereIn('id', $ids)->count();
        $this->assertNotEquals($countBefore, count($ids));

        $this->setResponse(
            path: '/api/client/campaign',
            body: $body,
        );

        $this->callMethod([$loader, 'start']);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->setResponse(
            path: '/api/client/token',
            body: [
                'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.WyJFTWhVVU...qTz2XXZBv41h4',
                'expires_in'   => 1800,
                'token_type'   => 'Bearer'
            ]
        );
    }
}
