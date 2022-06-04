<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropOldColumnsForStrategies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('strategies', function (Blueprint $table) {
            $table->dropColumn('threshold');
            $table->dropColumn('step');
        });

        Schema::dropIfExists('strategy_changes');

        Schema::table('strategy_shows_keyword_statistics', function (Blueprint $table) {
            $table->dropForeign('strategy_keyword_statistics_strategy_id_foreign');
            $table->dropColumn('strategy_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('strategies', function (Blueprint $table) {
            $table->double('threshold', 4, 2, true);
            $table->double('step', 6, 2);
        });

        Schema::table('strategy_changes', function(Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreign('strategy_id')->references('id')->on('strategies')->onUpdate('cascade')->cascadeOnDelete();
            $table->decimal('threshold', 4, 2, true)->nullable()->change();
            $table->decimal('step', 6, 2)->nullable()->change();
            $table->foreignId('status_id')->nullable()->constrained('strategy_statuses')->onUpdate('cascade')->cascadeOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('strategy_types')->onUpdate('cascade')->cascadeOnDelete();
        });
    }
}
