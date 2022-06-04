<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Requests\V1\Frontend\AnalyticsListRequest;
use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Helpers\AnalyticsHelper;
use App\Helpers\CampaignStatisticHelper;
use App\Helpers\ProductStatisticHelper;
use App\Helpers\KeywordStatisticHelper;
use App\Http\Controllers\Controller;
use App\Services\CalculateService;
use App\Models\Status;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticController extends Controller
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_COUNT = 15;

    /**
     * Страница Аналитики
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getAnalyticsList(AnalyticsListRequest $request)
    {
        $campaignIds = $request->campaigns;
        $currentPage = $request->page ?? static::DEFAULT_PAGE;
        $perPage = $request->per_page ?? static::DEFAULT_COUNT;
        $all = $request->all ?? false;
        $needReformat = $request->needReformat ?? true;
        $analyticsDataQuery = AnalyticsHelper::makeDetailedStatisticQuery($request);

        if ($all) {
            $analyticsData = $analyticsDataQuery->get();
        } else {
            $analyticsData = $analyticsDataQuery->paginate($perPage, ['*'], 'page', $currentPage);
        }

        $analyticsData->each(function ($analytic) use ($needReformat) {
            // Расчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($analytic);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignColumns($analytic);
            }
        });

        // Итоги
        $totals = AnalyticsHelper::makeStatisticTotalsQuery($request)->first();

        // Расчитать недостающие показатели
        CalculateService::recalcAllCharacteristics($totals);
        // Преобразовать значения для вывода на фронт
        if ($needReformat) {
            CalculateService::reassignColumns($totals);
        }

        $analyticsData = $analyticsData->toArray();
        $analyticsData['counters'] = $totals;
        $analyticsData['campaign'] = !empty($campaignIds[0]) ? Campaign::find($campaignIds[0]) : [];

        return response()->api_success(['campaigns' => $analyticsData]);
    }


    /**
     * Список рекламных компаний с
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getCampaignsList(Request $request)
    {
        $request->validate([
            'from' => 'date',
            'to' => 'date',
            'campaigns' => 'array',
            'campaignsStatuses' => 'array',
            'strategiesStatuses' => 'array',
            'placements' => 'array',
            'paymentsTypes' => 'array',
            'per_page' => 'integer',
            'page' => 'integer',
        ]);

        $currentPage = $request->page ?? static::DEFAULT_PAGE;
        $perPage = $request->per_page ?? static::DEFAULT_COUNT;
        $all = $request->all ?? false;

        $needReformat = $request->needReformat ?? true;

        $campaignsQuery = CampaignStatisticHelper::makeDetailedStatisticQuery($request);
        if ($all) {
            $campaignsData = $campaignsQuery
                ->addSelect('campaigns.station_type')
                ->get();
        } else {
            $campaignsData = $campaignsQuery->paginate($perPage, ['*'], 'page', $currentPage);
            // Итоги
            $totals = CampaignStatisticHelper::makeStatisticTotalsQuery($request)->first();

            // Рассчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($totals);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignColumns($totals);
            }
        }

        $campaignsData->each(function ($campaign) use ($needReformat) {
            // Рассчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($campaign);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignCampaignColumns($campaign);
            }
        });
        $campaignsData = $campaignsData->toArray();

        if (isset($totals)) {
            $campaignsData['counters'] = $totals;
        }

        return response()->json(
            [
                'success' => true,
                'count' => count($campaignsData),
                'data' => ['campaigns' => $campaignsData],
            ]
        );
    }

    /**
     * Страница Ставки. Уровень товаров
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getProductsList(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
            'campaigns' => 'array',
            'groups' => 'array',
            'products' => 'array',
            'statuses' => 'array',
            'price_from' => 'integer',
            'price_to' => 'integer',
            'per_page' => 'integer',
            'page' => 'integer',
        ]);

        // Параметры
        $from = $request->from;
        $to = $request->to;

        $campaignIds = $request->campaigns;
        $groupsIds = $request->groups;
        $campaignProductsIds = $request->products;
        $statusIds = $request->statuses ?? [Status::ACTIVE, Status::NOT_ACTIVE, Status::ARCHIVED];

        $priceFrom = $request->price_from;
        $priceTo = $request->price_to;

        $currentPage = $request->page ?? static::DEFAULT_PAGE;
        $perPage = $request->per_page ?? static::DEFAULT_COUNT;
        $all = $request->all ?? false;

        $needReformat = $request->needReformat ?? true;

        // Аккаунт
        $accountId = UserService::getCurrentAccount();

        // Кампании
        $campaigns = Campaign::query()
            ->whereIn('id', $campaignIds ?? [])
            ->select('id')
            ->selectRaw('CONCAT(name, " (", ozon_id, ")") as name')
            ->pluck('name', 'id')
            ->toArray();

        // Товары
        $productsQuery = ProductStatisticHelper::makeDetailedStatisticQuery(
            $from,
            $to,
            $campaignIds,
            $groupsIds,
            $campaignProductsIds,
            $statusIds,
            $priceFrom,
            $priceTo,
            $accountId
        );
        if ($all) {
            $productsData = $productsQuery->get();
        } else {
            $productsData = $productsQuery->paginate($perPage, ['*'], 'page', $currentPage);

            // Итоги
            $totals = ProductStatisticHelper::makeStatisticTotalsQuery(
                $from,
                $to,
                $campaignIds,
                $groupsIds,
                $campaignProductsIds,
                $statusIds,
                $priceFrom,
                $priceTo,
                $accountId
            )->first();

            // Расчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($totals);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignColumns($totals);
            }
        }

        $productsData->each(function ($product) use ($needReformat) {
            // Расчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($product);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignColumns($product);
            }
        });

        $productsData = $productsData->toArray();

        if (isset($totals)) {
            $productsData['counters'] = $totals;
        }

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'campaigns' => $campaigns,
                    'campaignNames' => mb_strimwidth(implode(', ', $campaigns), 0, 55, "..."),
                    'products' => $productsData
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * Страница Ставки. Уровень ключевых слов
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getKeywordsList(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
            'campaigns' => 'array',
            'products' => 'array',
            'statuses' => 'array',
            'per_page' => 'integer',
            'page' => 'integer',
        ]);

        // Параметры
        $from = $request->from;
        $to = $request->to;

        $campaignIds = $request->campaigns;
        $campaignProductsIds = $request->products;
        $statusIds = $request->statuses ?? [Status::ACTIVE, Status::NOT_ACTIVE, Status::ARCHIVED];

        $currentPage = $request->page ?? static::DEFAULT_PAGE;
        $perPage = $request->per_page ?? static::DEFAULT_COUNT;
        $all = $request->all ?? false;

        $needReformat = $request->needReformat ?? true;

        // Аккаунт
        $accountId = UserService::getCurrentAccount();

        // Кампании
        $campaigns = Campaign::query()
            ->whereIn('id', $campaignIds ?? [])
            ->select('id')
            ->selectRaw('CONCAT(name, " (", ozon_id, ")") as name')
            ->pluck('name', 'id')
            ->toArray();

        // Товары
        $products = CampaignProduct::query()
            ->whereIn('campaign_products.id', $campaignProductsIds ?? [])
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->pluck('products.name', 'campaign_products.id')
            ->toArray();

        // Ключевики
        $keywordsQuery = KeywordStatisticHelper::makeDetailedStatisticQuery(
            $from,
            $to,
            $campaignIds,
            $campaignProductsIds,
            $statusIds,
            $accountId
        );
        if ($all) {
            $keywordsData = $keywordsQuery->get();
        } else {
            $keywordsData = $keywordsQuery->paginate($perPage, ['*'], 'page', $currentPage);

            // Итоги
            $totals = KeywordStatisticHelper::makeStatisticTotalsQuery(
                $from,
                $to,
                $campaignIds,
                $campaignProductsIds,
                $statusIds,
                $accountId
            )->first();

            // Расчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($totals);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignColumns($totals);
            }
        }

        $keywordsData->each(function ($keyword) use ($needReformat) {
            // Расчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($keyword);

            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignColumns($keyword);
            }
        });

        $keywordsData = $keywordsData->toArray();

        if (isset($totals)) {
            $keywordsData['counters'] = $totals;
        }

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'campaigns' => $campaigns,
                    'campaignNames' => mb_strimwidth(implode(', ', $campaigns), 0, 55, "..."),
                    'products' => $products,
                    'productNames' => mb_strimwidth(implode(', ', $products), 0, 55, "..."),
                    'keywords' => $keywordsData,
                ],
                'errors' => [],
            ]
        );
    }

    /**
     * Страница Ставки. Уровень рекламных кампаний
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getCampaigns(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
            'campaigns' => 'array',
            'statuses' => 'array',
            'per_page' => 'integer',
            'page' => 'integer',
        ]);

        $from = $request->from;
        $to = $request->to;

        $statusesIds = $request->statuses;
        $campaignIds = $request->campaigns;

        $currentPage = $request->page ?? static::DEFAULT_PAGE;
        $perPage = $request->per_page ?? static::DEFAULT_COUNT;
        $all = $request->all ?? false;

        $needReformat = $request->needReformat ?? true;

        $accountId = UserService::getCurrentAccount();

        $campaignsQuery = CampaignStatisticHelper::makeDetailedStatisticQuery($from, $to, $campaignIds, $statusesIds,
            $accountId);
        if ($all) {
            $campaignsData = $campaignsQuery
                ->addSelect('campaigns.station_type')
                ->get();
        } else {
            $campaignsData = $campaignsQuery->paginate($perPage, ['*'], 'page', $currentPage);
            // Итоги
            $totals = CampaignStatisticHelper::makeStatisticTotalsQuery($from, $to, $campaignIds, $statusesIds,
                $accountId)->first();

            // Рассчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($totals);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignColumns($totals);
            }
        }

        $campaignsData->each(function ($campaign) use ($needReformat) {
            // Рассчитать недостающие показатели
            CalculateService::recalcAllCharacteristics($campaign);
            // Преобразовать значения для вывода на фронт
            if ($needReformat) {
                CalculateService::reassignCampaignColumns($campaign);
            }
        });

        $campaignsData = $campaignsData->toArray();

        if (isset($totals)) {
            $campaignsData['counters'] = $totals;
        }

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'campaigns' => $campaignsData,
                ],
            ]
        );
    }

    /**
     * Получение счетчиков
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getCounters(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'date',
            'to' => 'date',
            'campaigns' => 'array',
            'statuses' => 'array',
            'per_page' => 'integer',
            'page' => 'integer',
        ]);

        $from = $request->from;
        $to = $request->to;

        $statusesIds = $request->statuses ?? [Status::ACTIVE, Status::NOT_ACTIVE, Status::ARCHIVED];
        $campaignIds = $request->campaigns;

        $needReformat = $request->needReformat ?? true;

        $accountId = UserService::getCurrentAccount();

        $campaignsQuery = CampaignStatisticHelper::makeDetailedStatisticQuery($from, $to, $campaignIds, $statusesIds,
            $accountId);
        $campaignsData = $campaignsQuery
            ->addSelect('campaigns.station_type')
            ->get();

        // Итоги
        $totals = CampaignStatisticHelper::makeStatisticTotalsQuery($from, $to, $campaignIds, $statusesIds,
            $accountId)->first();

        // Рассчитать недостающие показатели
        CalculateService::recalcAllCharacteristics($totals);
        // Преобразовать значения для вывода на фронт
        if ($needReformat) {
            CalculateService::reassignColumns($totals);
        }

        if (isset($totals)) {
            $campaignsData['counters'] = $totals;
        }

        return response()->api_success($totals);
    }
}
