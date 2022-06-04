<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStrategiesTablesReferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('strategy_changes', function(Blueprint $table) {
            $table->dropForeign('strategy_history_strategy_id_foreign');
            $table->dropIndex('strategy_history_strategy_id_foreign');
            $table->foreign('strategy_id')->references('id')->on('strategies')->onUpdate('cascade')->cascadeOnDelete();
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
