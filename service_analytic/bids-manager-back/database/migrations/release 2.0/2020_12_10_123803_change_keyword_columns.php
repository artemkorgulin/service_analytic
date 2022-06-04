<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeKeywordColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keyword', function(Blueprint $table) {
            DB::statement('ALTER TABLE keyword CHANGE COLUMN created_at created_at TIMESTAMP NULL DEFAULT NULL AFTER id;');
            DB::statement('ALTER TABLE keyword CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;');
            DB::statement('ALTER TABLE keyword CHANGE COLUMN good_id good_id INT(11) NULL DEFAULT NULL AFTER `sku`;');
            DB::statement('ALTER TABLE keyword CHANGE COLUMN status status BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER `good_id`;');
            DB::statement('ALTER TABLE keyword CHANGE COLUMN bet bet INT(11) NULL DEFAULT NULL AFTER `status`;');
            $table->dropColumn('account_id');
            $table->renameColumn('status', 'status_id');
            $table->foreign('status_id')->references('id')->on('status');
            $table->renameColumn('bet', 'bid');
        });

        Schema::table('keyword_statistics', function(Blueprint $table) {
            $table->dropColumn('sku');
            $table->dropColumn('status_id');
            DB::statement('ALTER TABLE keyword_statistics CHANGE COLUMN keyword_id keyword_id BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER updated_at;');
            $table->foreign('keyword_id')->references('id')->on('keyword');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keyword', function(Blueprint $table) {
            $table->integer('account_id')->nullable();
            $table->renameColumn('status_id', 'status');
            $table->dropForeign('keyword_status_id_foreign');
            $table->renameColumn('bid', 'bet');
        });

        Schema::table('keyword_statistics', function(Blueprint $table) {
            $table->unsignedBigInteger('sku')->nullable();
            $table->string('status_id')->nullable();
            $table->dropForeign('keyword_statistics_keyword_id_foreign');
        });
    }
}
