<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldsV2ProductChangeHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_product_change_history', function (Blueprint $table) {
            $table->bigInteger('task_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_product_change_history', function (Blueprint $table) {
            $table->bigInteger('task_id')->nullable(false)->change();
        });
    }
}
