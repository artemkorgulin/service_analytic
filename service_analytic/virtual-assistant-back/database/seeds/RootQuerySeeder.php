<?php

use App\Models\OzAlias;
use App\Models\RootQuery;
use App\Models\SearchQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RootQuerySeeder extends Seeder
{
    protected function create($rootQuery, $searchQueries, $aliases = null): RootQuery
    {
        /** @var RootQuery $rootQuery */
        $rootQuery = RootQuery::create($rootQuery);

        collect($searchQueries)
            ->each(function ($arSearchQuery) use ($rootQuery)
            {
                $pivot = $arSearchQuery['pivot'];
                unset($arSearchQuery['pivot']);

                /** @var SearchQuery $searchQuery */
                $searchQuery = SearchQuery::create($arSearchQuery);
                $rootQuery->searchQueries()->attach($searchQuery, $pivot);
            });

        if ($aliases) {
            $rootQuery->aliases()->saveMany(
                collect($aliases)->map(function ($data) {
                    return new OzAlias($data);
                })
            );
        }

        return $rootQuery;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('root_queries')->truncate();
        DB::table('search_queries')->truncate();
        DB::table('aliases')->truncate();
        Schema::enableForeignKeyConstraints();

        // одежда, обувь и аксессуары
        $this->create(
            [
                'id' => 1,
                'ozon_category_id' => 1,
                'name' => 'блеск',
                'is_visible' => 1
            ],
            [
                [
                    'id'          => 1,
                    'name'        => 'блеск для обуви',
                    'is_negative' => 0,
                    'pivot' => [
                        'popularity'        => 4,
                        'additions_to_cart' => 2,
                        'avg_price'         => 264,
                        'products_count'    => 4,
                        'rating'            => 4,
                    ]
                ],
            ]
        );

        $this->create(
            [
                'id' => 2,
                'ozon_category_id' => 1,
                'name' => 'талисман',
                'is_visible' => 1
            ],
            [
                [
                    'id'                => 2,
                    'name'              => 'обереги амулеты',
                    'is_negative'       => 0,
                    'pivot' => [
                        'popularity'        => 45,
                        'additions_to_cart' => 5,
                        'avg_price'         => 156,
                        'products_count'    => 8,
                        'rating'            => 2,
                    ]
                ],
                [
                    'id'                => 3,
                    'name'              => 'славянские амулеты',
                    'is_negative'       => 0,
                    'pivot' => [
                        'popularity'        => 14,
                        'additions_to_cart' => 1,
                        'avg_price'         => 454,
                        'products_count'    => 2,
                        'rating'            => 1,
                    ]
                ],
            ],
            [
                ['name' => 'амулет']
            ]
        );

        // электроника
        $this->create(
            [
                'id' => 3,
                'ozon_category_id' => 2,
                'name' => 'smartwatch',
                'is_visible' => 1
            ],
            [
                [
                    'id'                => 4,
                    'name'              => 'детские смарт часы',
                    'is_negative'       => 0,
                    'pivot' => [
                        'popularity'        => 209,
                        'additions_to_cart' => 37,
                        'avg_price'         => 2155,
                        'products_count'    => 1,
                        'rating'            => 1369,
                    ]
                ],
                [
                    'id'                => 5,
                    'name'              => 'детские смарт часы с gps',
                    'is_negative'       => 0,
                    'pivot' => [
                        'popularity'        => 15,
                        'additions_to_cart' => 1,
                        'avg_price'         => 1989,
                        'products_count'    => 1,
                        'rating'            => 1,
                    ]
                ],
                [
                    'id'                => 6,
                    'name'              => 'смарт часы xiaomi',
                    'is_negative'       => 1,
                    'pivot' => [
                        'popularity'        => 131,
                        'additions_to_cart' => 13,
                        'avg_price'         => 2829,
                        'products_count'    => 1,
                        'rating'            => 169,
                    ]
                ],
            ],
            [
                ['name' => 'смарт часы'],
                ['name' => 'часы смарт'],
            ]);

        $this->create(
            [
                'id' => 4,
                'ozon_category_id' => 2,
                'name' => 'фильтр',
                'is_visible' => 1
            ],
            [
                [
                    'id'                => 7,
                    'name'              => 'переходник для розетки',
                    'is_negative'       => 0,
                    'pivot' => [
                        'popularity'        => 1200,
                        'additions_to_cart' => 30,
                        'avg_price'         => 255,
                        'products_count'    => 10,
                        'rating'            => 1579,
                    ]
                ],
                [
                    'id'                => 8,
                    'name'              => 'сетевой фильтр',
                    'is_negative'       => 0,
                    'pivot' => [
                        'popularity'        => 1345,
                        'additions_to_cart' => 20,
                        'avg_price'         => 1203,
                        'products_count'    => 26,
                        'rating'            => 268,
                    ]
                ],
                [
                    'id'                => 9,
                    'name'              => 'сетевой фильтр с предохранителем',
                    'is_negative'       => 0,
                    'pivot' => [
                        'popularity'        => 1040,
                        'additions_to_cart' => 13,
                        'avg_price'         => 1650,
                        'products_count'    => 12,
                        'rating'            => 169,
                    ]
                ],
            ],
            [
                ['name' => 'удлинитель'],
                ['name' => 'переходник'],
                ['name' => 'фильтры'],
            ]);

        $this->create(
            [
                'id' => 5,
                'ozon_category_id' => 2,
                'name' => 'светофильтр',
                'is_visible' => 1
            ],
            [],
            [
                ['name' => 'светофильтры'],
            ]);

        // дом и сад
        $this->create(
            [
                'id' => 6,
                'ozon_category_id' => 3,
                'name' => 'фильтр',
                'is_visible' => 1
            ],
            [],
            [
                ['name' => 'фильтры'],
            ]);

        // бытовая техника
        $this->create(
            [
                'id' => 7,
                'ozon_category_id' => 6,
                'name' => 'фильтр',
                'is_visible' => 1
            ],
            [],
            [
                ['name' => 'фильтры'],
            ]);
    }
}
