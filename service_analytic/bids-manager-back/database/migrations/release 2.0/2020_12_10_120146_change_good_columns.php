<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeGoodColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('good', function(Blueprint $table) {
            DB::statement('ALTER TABLE good CHANGE COLUMN created_at created_at TIMESTAMP NULL DEFAULT NULL AFTER id;');
            DB::statement('ALTER TABLE good CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;');
            DB::statement('ALTER TABLE good CHANGE COLUMN sku sku VARCHAR(255) NULL DEFAULT NULL COLLATE "utf8mb4_unicode_ci" AFTER name;');
            DB::statement('ALTER TABLE good CHANGE COLUMN campaign_id campaign_id BIGINT(20) UNSIGNED NOT NULL AFTER sku;');
//            $table->dropIndex('good_keyword_id_foreign');
            $table->dropColumn('keyword_id');
            $table->dropColumn('account_id');
//            $table->foreign('campaign_id')->references('id')->on('campaign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
