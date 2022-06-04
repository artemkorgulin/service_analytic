<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDbStructureForGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->rename('campaigns');
        });

        Schema::table('good', function (Blueprint $table) {
            $table->rename('campaign_goods');
        });

        Schema::table('keyword', function (Blueprint $table) {
            $table->rename('campaign_keywords');
        });

        Schema::table('stop_words', function (Blueprint $table) {
            $table->rename('campaign_stop_words');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('ozon_id')->unsigned()->nullable()->index('category_ozon_id');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name', 50);
        });

        Schema::create('campaign_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->index('campaign_status_code');
            $table->string('ozon_code', 50)->index('campaign_status_ozon_code');
            $table->string('name', 50);
        });

        Schema::create('good_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->index('good_status_code');
            $table->string('ozon_code', 50)->index('good_status_ozon_code');
            $table->string('name', 50);
        });

        Schema::table('status', function (Blueprint $table) {
            $table->string('status', 50)->change();
            $table->string('name', 50)->change();
            $table->renameColumn('status', 'code');
            $table->rename('statuses');
        });

        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('sku', 20)->index('good_sku');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->string('offer_id')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('good_status_id')->nullable()->constrained('good_statuses')->onUpdate('cascade')->onDelete('restrict');
            $table->decimal('price');
            $table->boolean('visible')->default(true);
            $table->foreignId('account_id')->constrained('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->index(['account_id', 'sku'], 'good_supplier_sku');
            $table->index(['account_id', 'product_id', 'sku'], 'good_supplier_product_sku');
        });

        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->unique('keyword_name');
        });

        Schema::create('stop_words', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->unique('stop_word_name');
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->nullable();
            $table->foreignId('campaign_id')->constrained('campaigns')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('ozon_id')->unsigned()->nullable()->index('group_ozon_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            DB::raw('ALTER TABLE campaigns CHANGE COLUMN created_at created_at TIMESTAMP NULL DEFAULT NULL AFTER id, CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;');
//            $table->dropForeign('campaign_status_id_foreign');
            $table->dropColumn('status_id');
//          $table->foreignId('campaign_status_id')->nullable()->after('account_id')->constrained('campaign_statuses')->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('campaign_goods', function (Blueprint $table) {
            $table->foreignId('good_id')->nullable()->after('campaign_id')->constrained('goods')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->after('good_id')->constrained('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->dropColumn('date');
        });

        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->dropColumn('user_query');
            $table->foreignId('group_id')->nullable()->after('good_id')->constrained('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('keyword_id')->nullable()->after('group_id')->constrained('keywords')->onUpdate('cascade')->onDelete('cascade');
            $table->renameColumn('good_id', 'campaign_good_id');
        });

        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('stop_word_id')->nullable()->constrained('stop_words')->onUpdate('cascade')->onDelete('cascade');
            $table->renameColumn('good_id', 'campaign_good_id');
        });

        Schema::table('keyword_statistics', function (Blueprint $table) {
            $table->renameColumn('keyword_id', 'campaign_keyword_id');
            $table->dropColumn('avg_1000_shows_price');
            $table->dropColumn('avg_click_price');
            $table->dropColumn('purchased_shows');
            $table->rename('campaign_keyword_statistics');
        });

        Schema::table('good_statistics', function (Blueprint $table) {
            $table->renameColumn('good_id', 'campaign_good_id');
            $table->dropColumn('ctr');
            $table->dropColumn('avg_1000_shows_price');
            $table->dropColumn('avg_click_price');
            $table->dropColumn('purchased_shows');
            $table->rename('campaign_good_statistics');
        });

        Schema::table('campaign_statistics', function (Blueprint $table) {
            $table->dropColumn('ctr');
            $table->dropColumn('avg_1000_shows_price');
            $table->dropColumn('avg_click_price');
            $table->dropColumn('purchased_shows');
        });

        Schema::table('strategy_keyword_statistics', function (Blueprint $table) {
            $table->renameColumn('keyword_id', 'campaign_keyword_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign('campaigns_campaign_status_id_foreign');
            $table->dropColumn('campaign_status_id');
            $table->foreignId('status_id')->nullable()->after('name')->constrained('statuses')->onUpdate('cascade')->onDelete('restrict');
            $table->rename('campaign');
        });

        Schema::table('campaign_goods', function (Blueprint $table) {
            $table->dropForeign('campaign_goods_good_id_foreign');
            $table->dropForeign('campaign_goods_group_id_foreign');
            $table->dropColumn('good_id');
            $table->dropColumn('group_id');
            $table->rename('good');
        });

        Schema::table('campaign_keywords', function (Blueprint $table) {
            $table->dropForeign('campaign_keywords_keyword_id_foreign');
            $table->dropColumn('keyword_id');
            $table->dropForeign('campaign_keywords_group_id_foreign');
            $table->dropColumn('group_id');
            $table->renameColumn('campaign_good_id', 'good_id');
            $table->rename('keyword');
        });

        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->dropForeign('campaign_stop_words_stop_word_id_foreign');
            $table->dropColumn('stop_word_id');
            $table->dropForeign('campaign_stop_words_group_id_foreign');
            $table->dropColumn('group_id');
            $table->renameColumn('campaign_good_id', 'good_id');
            $table->rename('stop_words');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->renameColumn('code', 'status');
            $table->rename('status');
        });

        Schema::table('campaign_good_statistics', function (Blueprint $table) {
            $table->renameColumn('campaign_good_id', 'good_id');
            $table->rename('good_statistics');
        });

        Schema::table('campaign_keyword_statistics', function (Blueprint $table) {
            $table->renameColumn('campaign_keyword_id', 'keyword_id');
            $table->rename('keyword_statistics');
        });

        Schema::table('strategy_keyword_statistics', function (Blueprint $table) {
            $table->renameColumn('campaign_keyword_id', 'keyword_id');
        });

        Schema::dropIfExists('groups');
        Schema::dropIfExists('keywords');
        Schema::dropIfExists('stop_words');
        Schema::dropIfExists('goods');
        Schema::dropIfExists('campaign_statuses');
        Schema::dropIfExists('good_statuses');
        Schema::dropIfExists('categories');
    }
}
