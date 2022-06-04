<?php

namespace Tests\Feature\Autoselect;

use App\Traits\UserForTest;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetAutoselectResultsXLSTest extends TestCase
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
        $response = $this->json('POST', config('app.url') . '/api/autoselect/results/sheet',
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
                    'xlsLink',
                    'xlsName'
                ],
                'errors',
            ]);
    }

    public function testMissedAutoselectParameterIdValidation()
    {
        $randomDataFromDb = $this->prepareRandomRequestData();
        unset($randomDataFromDb['autoselect_parameter_id']);
        $response = $this->json('POST', config('app.url') . '/api/autoselect/results/sheet',
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
                'data',
                'errors' => [
                    'autoselect_parameter_id'
                ],
            ]);

    }

    /**
     * @return array
     */
    protected function prepareRandomRequestData(): array
    {
        return array(
            'autoselect_parameter_id' => DB::table('autoselect_parameters')->inRandomOrder()->limit(1)->value('id'),
        );
    }
}
