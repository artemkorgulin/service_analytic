<?php

namespace Tests\Feature\Autoselect;

use App\Traits\UserForTest;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetFilteredAutoselectResultsTest extends TestCase
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
        $response = $this->json('POST', config('app.url') . '/api/autoselect/results',
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


    public function testMissedAutoselectParameterId()
    {
        $response = $this->json('POST', config('app.url') . '/api/autoselect/results',
            array_merge(
                [
                    'api_token' => $this->getToken(),
                    'uuid'      => '81590d82-1f33-4ffc-8ce4-1ce47f5b8888',
                ],
            )
        );

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'data'   => [],
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
        $tableFields = [
            'popularity', 'cart_add_count', 'avg_cost', 'crtc',
            'category_popularity', 'category_cart_add_count', 'category_avg_cost', 'category_crtc'
        ];
        $sortDirections = ['asc', 'desc'];
        $filterOperations = ['<', '>', '=', '!='];
        return array(
            'autoselect_parameter_id' => DB::table('autoselect_parameters')->inRandomOrder()->limit(1)->value('id'),
            'order'                   => [
                'field'     => $tableFields[array_rand($tableFields)],
                'direction' => $sortDirections[array_rand($sortDirections)],
            ],
            'filter'                  => [
                [
                    'column'    => $tableFields[array_rand($tableFields)],
                    'operation' => $filterOperations[array_rand($filterOperations)],
                    'value'     => rand(0, 1000),
                ],
                [
                    'column'    => $tableFields[array_rand($tableFields)],
                    'operation' => $filterOperations[array_rand($filterOperations)],
                    'value'     => rand(0, 1000),
                ],
            ]
        );
    }
}
