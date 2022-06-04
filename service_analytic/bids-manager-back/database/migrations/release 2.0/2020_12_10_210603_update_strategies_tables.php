<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStrategiesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keyword_statistics', function(Blueprint $table) {
            $table->integer('popularity')->unsigned()->nullable();
            $table->double('purchased_shows')->unsigned()->nullable();
        });
        Schema::table('good_statistics', function(Blueprint $table) {
            $table->integer('popularity')->unsigned()->nullable();
            $table->double('purchased_shows')->unsigned()->nullable();
        });
        Schema::table('campaign_statistics', function(Blueprint $table) {
            $table->integer('popularity')->unsigned()->nullable();
            $table->double('purchased_shows')->unsigned()->nullable();
        });

        Schema::table('strategies', function(Blueprint $table) {
            $table->integer('budget')->unsigned()->nullable();
        });

        Schema::table('strategy_history', function(Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('popularity_yesterday');
            $table->dropColumn('popularity_last_week');
            $table->rename('strategy_changes');
        });

        Schema::create('strategy_keyword_statistics', function(Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_id')->constrained('strategies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('keyword_id')->constrained('keyword')->onUpdate('cascade')->onDelete('cascade');
            $table->date('date');
            $table->integer('bid')->unsigned();
            $table->integer('popularity')->unsigned();
            $table->double('threshold', 4, 2, true);
            $table->double('step', 6, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keyword_statistics', function(Blueprint $table) {
            $table->dropColumn('popularity');
            $table->dropColumn('purchased_shows');
        });
        Schema::table('good_statistics', function(Blueprint $table) {
            $table->dropColumn('popularity');
            $table->dropColumn('purchased_shows');
        });
        Schema::table('campaign_statistics', function(Blueprint $table) {
            $table->dropColumn('popularity');
            $table->dropColumn('purchased_shows');
        });

        Schema::table('strategies', function(Blueprint $table) {
            $table->dropColumn('budget');
        });

        Schema::table('strategy_changes', function(Blueprint $table) {
            $table->date('date');
            $table->integer('popularity_yesterday', false, true);
            $table->integer('popularity_last_week', false, true);
            $table->rename('strategy_history');
        });

        Schema::dropIfExists('strategy_keyword_statistics');
    }
}
