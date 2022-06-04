<?php

namespace App\Services;

use App\Helpers\QueryBuilderHelper;
use App\Helpers\StatisticQueries;
use App\Models\CardProduct;
use App\Models\Position;
use App\Repositories\V1\Assistant\WbProductRepository;
use Illuminate\Support\Facades\DB;

/**
 * Class UserService
 * @package App\Services
 */
class PositionService
{
    /**
     * Получить топ товаров wb.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return array
     */
    public function getTopWb(int $userId, int $limit): array
    {
        $nmids = QueryBuilderHelper::getUserProducts($userId);

        $positionCategories = Position::selectRaw('positions.id, positions.vendor_code, positions.position,
                                                   positions.date, positions.created_at, method_breadcrumbs_category.subject,
                                                   method_breadcrumbs_category.category')
        ->join(DB::raw(StatisticQueries::getBreadcrumbsCategory('positions')),
            'positions.web_id', '=', 'method_breadcrumbs_category.web_id')
        ->whereIn('vendor_code', $nmids)
        ->orderByDesc('positions.date')
        ->orderBy('positions.position')
        ->limit($limit)
        ->get();

        $vendorCodes = $positionCategories->pluck('vendor_code');

        $cardProducts = CardProduct::whereIn('vendor_code', $vendorCodes)->get()->keyBy('vendor_code');

        $wbProducts = (new WbProductRepository())->getProductsByVendorCodes($vendorCodes);
        $maxDate = null;
        $firstPositionCategories = $positionCategories->first();
        if ($firstPositionCategories) {
            $maxDate = $firstPositionCategories->date;
        }

        $result = [];
        foreach ($positionCategories as $positionCategory) {
            if ($positionCategory->date == $maxDate) {
                $el = $positionCategory->toArray();

                $el['product_id'] = $wbProducts[$positionCategory->vendor_code]->id;
                $el['product_name'] = $cardProducts->get($positionCategory->vendor_code)?->name ?? '';
                $el['photo_url'] = QueryBuilderHelper::generateWbImageUrl($positionCategory->vendor_code);
                $el['sku'] = $positionCategory->vendor_code;
                $el['optimization'] = $wbProducts[$positionCategory->vendor_code]->optimization;
                $el['marketplace'] = 'wb';

                $result[] = $el;
            }
        }

        return $result;
    }

    /**
     * Получить топ товаров oz.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return array
     */
    public function getTopOzon(int $userId, int $limit): array
    {
        $productPositionHistoryGraphs = DB::connection('va')
            ->table('oz_product_positions_history_graph')
            ->select('oz_product_positions_history_graph.id', 'oz_product_positions_history_graph.product_id',
                'oz_product_positions_history_graph.position',
                'oz_product_positions_history_graph.category', 'oz_product_positions_history_graph.date',
                'oz_product_positions_history_graph.created_at',
                'oz_products.name as product_name', 'oz_products.photo_url', 'oz_products.sku_fbo AS sku',
                'oz_products.optimization', DB::raw('\'oz\' as marketplace'))
            ->join('oz_products', 'oz_products.id', '=', 'oz_product_positions_history_graph.product_id')
            ->where('oz_products.user_id', '=', $userId)
            ->whereNull('oz_products.deleted_at')
            ->orderByDesc('oz_product_positions_history_graph.date')
            ->orderBy('oz_product_positions_history_graph.position')
            ->limit($limit)
            ->get();

        $maxDate = null;
        $firstPositionCategories = $productPositionHistoryGraphs->first();
        if ($firstPositionCategories) {
            $maxDate = $firstPositionCategories->date;
        }

        $result = [];
        foreach ($productPositionHistoryGraphs as $productPositionHistoryGraph) {
            if ($productPositionHistoryGraph->date == $maxDate) {
                $result[] = (array) $productPositionHistoryGraph;
            }
        }

        return $result;
    }
}
