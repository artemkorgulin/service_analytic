<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStrategyChangesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('strategy_changes', function(Blueprint $table) {
            $table->foreignId('status_id')->nullable()->constrained('strategy_statuses')->onUpdate('cascade')->cascadeOnDelete();
            $table->decimal('threshold', 4, 2, true)->nullable()->change();
            $table->decimal('step', 6, 2)->nullable()->change();
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
