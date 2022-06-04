<?php

use AnalyticPlatform\LaravelHelpers\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropGoodsAndCategoriesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (MigrationHelper::hasForeign('campaign_goods', 'campaign_goods_good_id_foreign')) {
            Schema::table('campaign_goods', function (Blueprint $table) {
                $table->dropForeign('campaign_goods_good_id_foreign');
            });
        }

        if (MigrationHelper::hasForeign('autoselect_parameters', 'autoselect_parameters_category_id_foreign')) {
            Schema::table('autoselect_parameters', function (Blueprint $table) {
                $table->dropForeign('autoselect_parameters_category_id_foreign');
            });
        }

        if (MigrationHelper::hasForeign('keywords', 'keywords_category_id_foreign')) {
            Schema::table('keywords', function (Blueprint $table) {
                $table->dropForeign('keywords_category_id_foreign');
            });
        }

        Schema::dropIfExists('goods');
        Schema::dropIfExists('good_statuses');
        Schema::dropIfExists('categories');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ozon_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 50);

            $table->foreignId('parent_id')->nullable()->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('good_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->index('good_status_code');
            $table->string('ozon_code', 50)->index('good_status_ozon_code');
            $table->string('name', 50);
        });

        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 20)->index('good_sku');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->string('offer_id')->nullable();
            $table->decimal('price');
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->foreignId('category_id')->nullable()->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('good_status_id')->nullable()->constrained('good_statuses')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('account_id')->constrained('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->index(['account_id', 'sku'], 'good_supplier_sku');
            $table->index(['account_id', 'product_id', 'sku'], 'good_supplier_product_sku');
        });
    }
}
