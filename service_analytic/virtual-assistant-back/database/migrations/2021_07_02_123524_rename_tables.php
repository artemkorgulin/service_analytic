<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTables extends Migration
{

    protected array $oldNameToNewName = [
        'aliases' => 'oz_aliases',
        'characteristic_root_query_search_query' => 'oz_characteristic_root_query_search_query',
        'ozon_categories' => 'oz_data_categories',
        'ozon_seller_categories' => 'oz_data_seller_categories',
        'v2_ozon_categories' => 'oz_categories',
        'v2_ozon_category_to_feature' => 'oz_category_to_feature',
        'v2_ozon_feature_options' => 'oz_feature_options',
        'v2_ozon_feature_to_option' => 'oz_feature_to_option',
        'v2_ozon_features' => 'oz_features',
        'v2_product_change_history' => 'oz_product_change_history',
        'v2_product_feature_error_history' => 'oz_product_feature_error_history',
        'v2_product_feature_history' => 'oz_product_feature_history',
        'v2_product_positions_history' => 'oz_product_positions_history',
        'v2_product_positions_history_graph' => 'oz_product_positions_history_graph',
        'v2_product_price_change_history' => 'oz_product_price_change_history',
        'v2_product_statuses' => 'oz_product_statuses',
        'v2_products' => 'oz_products',
        'v2_products_features' => 'oz_products_features',
        'v2_temporary_products' => 'oz_temporary_products',
        'v2_trigger_change_feature' => 'oz_trigger_change_feature',
        'v2_trigger_change_feature_option' => 'oz_trigger_change_feature_option',
        'v2_trigger_change_min_photos' => 'oz_trigger_change_min_photos',
        'v2_trigger_change_min_reviews' => 'oz_trigger_change_min_reviews',
        'v2_trigger_remove_from_sale' => 'oz_trigger_remove_from_sale',
        'v2_web_categories' => 'oz_web_categories',
        'v2_web_categories_history' => 'oz_web_categories_history',
        'wb_cards' => 'wb_products',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        foreach ($this->oldNameToNewName as $old => $new) {
            if (Schema::hasTable($old)) {
                echo("Rename {$old} table to {$new}  \n");
                try {
                    Schema::rename($old, $new);
                } catch (\Exception $e) {
                    echo("{$e->getMessage()}");
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->oldNameToNewName as $old => $new) {
            if (Schema::hasTable($new)) {
                echo("Rename {$new} table to {$old} \n");
                try {
                    Schema::rename($new, $old);
                } catch (\Exception $e) {
                    echo("{$e->getMessage()}");
                }
            }
        }
    }
}
