<?php

namespace App\Services\V2;

use Illuminate\Support\Facades\DB;

/**
 * Class for Ozon products CRUD
 */
class OzonProductService
{

    /**
     * Delete Ozon products by external_id
     * @param $externalId
     */
    public static function destroyByExternalId($externalId)
    {
        $products = DB::table('oz_products')->select(['id'])->where('external_id', $externalId)->get();
        foreach ($products as $product) {
            try {
                self::_destroy($product->id);
            } catch (\Exception $exception) {
                report($exception);
            }
        }
        DB::table('oz_products')->where('external_id', $externalId)->delete();
    }


    /**
     * Delete Ozon products by id
     * @param $externalId
     */
    public static function destroyById($id)
    {
        $products = DB::table('oz_products')->select(['id'])->where('id', $id)->get();
        foreach ($products as $product) {
            try {
                self::_destroy($product->id);
            } catch (\Exception $exception) {
                report($exception);
            }
        }
        DB::table('oz_products')->where('id', $id)->delete();
    }

    /**
     * Destroy product
     * @param $productId
     */
    private static function _destroy(int $productId)
    {
        $ids = DB::table('oz_product_change_history')
            ->where('product_id', $productId)->pluck('id')->toArray();
        DB::table('oz_product_feature_history')->whereIn('history_id', $ids)->delete();
        DB::table('oz_product_feature_error_history')->whereIn('history_id', $ids)->delete();
        DB::table('oz_product_price_change_history')->whereIn('product_history_id', $ids)->delete();
        DB::table('oz_product_change_history')->where('product_id', $productId)->delete();
        DB::table('oz_products_features')->where('product_id', $productId)->delete();
        DB::table('oz_trigger_remove_from_sale')->where('product_id', $productId)->delete();
        DB::table('oz_trigger_change_min_photos')->where('product_id', $productId)->delete();
        DB::table('oz_trigger_change_min_reviews')->where('product_id', $productId)->delete();
        DB::table('oz_product_positions_history_graph')->where('product_id', $productId)->delete();
        DB::table('oz_product_positions_history')->where('product_id', $productId)->delete();
    }


}
