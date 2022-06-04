<?php

namespace App\Http\Controllers\Frontend;

use App\Models\CampaignProduct;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Список всех товаров
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getAllProductsList(Request $request)
    {
        $request->validate(
            [
                'campaigns.*' => 'integer',
                'group_id' => 'integer',
                'title' => 'string',
            ]
        );

        $campaignsIds = $request->campaigns;
        $groupId = $request->group_id;
        $productIds = (array)$request->productId;
        $productTitle = $request->title;
        $accountId = UserService::getCurrentAccountId();

        $productsById = CampaignProduct::query()
            ->whereIn('campaign_products.id', $productIds);

        $productsWithLimit = CampaignProduct::query()
            ->whereHas('campaign', function (Builder $query) use ($campaignsIds, $accountId) {
                $query->where('account_id', $accountId)
                    ->when($campaignsIds, function (Builder $query) use ($campaignsIds) {
                        $query->whereIn('campaigns.id', $campaignsIds);
                    });
            })
            ->whereHas('product', function (Builder $query) use ($productTitle) {
                $query->when($productTitle, function (Builder $query) use ($productTitle) {
                    $query->where('products.name', 'like', '%' . $productTitle . '%');
                });
            })
            ->when($groupId, function (Builder $query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
            ->whereNotIn('status_id', [Status::DELETED])
            ->take(Constants::TAKE_COUNT)
            ->union($productsById);

        $products = CampaignProduct::query()
            ->fromSub($productsWithLimit, 'campaign_products')
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->select('campaign_products.id')
            ->selectRaw('CONCAT(products.name, " (", products.sku, ")") as name')
            ->orderBy('products.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'errors' => [],
        ]);
    }

    /**
     * Получить названия колонок для фронта
     *
     * @return JsonResponse
     */
    public function getCountersDecode()
    {
        $countersDecode = static::getAggregateList();

        return response()->api_success($countersDecode);
    }

    /**
     * Подписи на фронте
     *
     * @return array
     */
    public static function getAggregateList()
    {
        return [
            'date' => __('front.date'),
            'shows' => __('front.shows'),
            'clicks' => __('front.clicks'),
            'ctr' => __('front.ctr'),
            'avg_click_price' => __('front.avg_click_price'),
            'avg_1000_shows_price' => __('front.avg_1000_shows_price'),
            'cost' => __('front.cost'),
            'orders' => __('front.orders'),
            'profit' => __('front.profit'),
            'cpo' => __('front.cpo'),
            'acos' => __('front.acos'),
            'popularity' => __('front.popularity'),
            'purchased_shows' => __('front.purchased_shows'),
            'campaign_name' => __('front.campaign_name'),
            'campaign_status_name' => __('front.campaign_status'),
            'campaign_page_type_name' => __('front.page_type'),
            'start_date' => __('front.start_date'),
            'end_date' => __('front.end_date'),
            'daily_budget' => __('front.daily_budget'),
            'product_name' => __('front.product_name'),
            'status_name' => __('front.status'),
            'keyword_name' => __('front.keyword_name'),
            'category_name' => __('front.category'),
            'group_name' => __('front.group'),
            'price' => __('front.price'),
            'bid' => __('front.bid'),

            'campaign_id' => __('front.campaign_id'),
            'strategy_status_id' => __('front.strategy_status_id'),
            'threshold' => __('front.threshold'),
            'step' => __('front.step'),
            'activation_date' => __('front.activation_date'),
            'change_date' => __('front.change_date'),
            'days_since_last_change' => __('front.days_since_last_change'),
            'avg_shows' => __('front.avg_shows'),
            'avg_popularity' => __('front.avg_popularity'),
            'avg_purchased_shows' => __('front.avg_purchased_shows'),
            'vr' => __('front.vr'),
            'horizon' => __('front.horizon'),
            'fcpo' => __('front.fcpo'),
            'tcpo' => __('front.tcpo'),
            'kvcr' => __('front.kvcr'),
        ];
    }
}
