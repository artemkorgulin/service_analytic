<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpoStrategyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('strategies_shows', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_id')->constrained('strategies')->onUpdate('cascade')->onDelete('cascade');
            $table->double('threshold', 4, 2, true);
            $table->double('step', 6, 2);
        });

        Schema::create('strategies_cpo', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_id')->constrained('strategies')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('tcpo')->unsigned()->default(100)->comment('tCPO');
            $table->integer('vr')->unsigned()->default(15)->comment('VR, %');
            $table->integer('horizon')->unsigned()->default(30)->comment('Горизонт оценки статистики');
            $table->integer('threshold1')->unsigned()->default(50)->comment('Порог принятия решения (p1)');
            $table->integer('threshold2')->unsigned()->default(100)->comment('Порог принятия решения (p2)');
            $table->integer('threshold3')->unsigned()->default(150)->comment('Порог принятия решения (p3)');
        });

        Schema::create('strategy_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_id')->constrained('strategies')->onUpdate('cascade')->onDelete('cascade');
            $table->string('parameter');
            $table->double('value_before');
            $table->double('value_after');
        });

        Schema::create('strategy_cpo_statistics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_cpo_id')->constrained('strategies_cpo')->onUpdate('cascade')->onDelete('cascade');
            $table->date('date');
            $table->double('fcpo')->unsigned()->comment('fCPO');
            $table->double('conversion')->unsigned()->comment('Конверсия по показам, %');
            $table->integer('orders')->unsigned()->comment('Количество заказов за горизонт оценки');

            $table->index('date');
            $table->index(['strategy_cpo_id', 'date']);
        });

        Schema::create('strategy_cpo_keyword_statistics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('strategy_cpo_id')->constrained('strategies_cpo')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('campaign_keyword_id')->constrained('campaign_keywords')->onUpdate('cascade')->onDelete('restrict');
            $table->date('date');
            $table->integer('bid')->unsigned()->comment('Ставка');
            $table->double('fcpo')->unsigned()->comment('fCPO');
            $table->double('conversion')->unsigned()->comment('Конверсия по показам, %');

            $table->index('date');
            $table->index(['strategy_cpo_id', 'date']);
            $table->index(['campaign_keyword_id', 'date']);
        });

        Schema::table('strategy_keyword_statistics', function (Blueprint $table) {
            $table->rename('strategy_shows_keyword_statistics');
        });

        Schema::table('strategy_shows_keyword_statistics', function (Blueprint $table) {
            $table->foreignId('strategy_shows_id')->nullable()->after('strategy_id')->constrained('strategies_shows')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('strategy_shows');
        Schema::dropIfExists('strategy_cpo');
        Schema::dropIfExists('strategy_histories');
        Schema::dropIfExists('strategy_cpo_statistics');
        Schema::dropIfExists('strategy_cpo_keyword_statistics');

        Schema::table('strategy_shows_keyword_statistics', function (Blueprint $table) {
            $table->dropForeign('strategy_shows_keyword_statistics_strategy_shows_id_foreign');
            $table->dropColumn('strategy_shows_id');
            $table->rename('strategy_keyword_statistics');
        });
    }
}
