<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStrategyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('strategy_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
        });

        Schema::create('strategy_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
        });

        Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_type_id')->constrained('strategy_types')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('strategy_status_id')->constrained('strategy_statuses')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('campaign_id')->constrained('campaign')->onUpdate('cascade')->onDelete('restrict');
            $table->dateTime('activated_at');
            $table->double('threshold', 4, 2, true);
            $table->double('step', 6, 2);
        });

        Schema::create('strategy_history', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_id')->constrained('strategies')->onUpdate('cascade')->onDelete('cascade');
            $table->date('date');
            $table->double('threshold', 4, 2, true);
            $table->double('step', 6, 2);
            $table->integer('popularity_yesterday', false, true);
            $table->integer('popularity_last_week', false, true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('strategy_history');
        Schema::dropIfExists('strategies');
        Schema::dropIfExists('strategy_types');
        Schema::dropIfExists('strategy_statuses');
    }
}
