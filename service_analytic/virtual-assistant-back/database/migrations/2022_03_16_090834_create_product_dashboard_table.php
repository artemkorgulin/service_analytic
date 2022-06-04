<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDashboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_dashboard', function (Blueprint $table) {
            $table->id();
            $table->string('dashboard_type')->index()->comment('Тип дашборда');
            $table->json('good_segment')->comment('Данные сегмента good для дашборда');
            $table->json('normal_segment')->comment('Данные сегмента normal для дашборда');
            $table->json('bad_segment')->comment('Данные сегмента bad для дашборда');
            $table->unsignedBigInteger('marketplace_platform_id')->index()->comment('Связь с таблицей webapp.platform');
            $table->unsignedBigInteger('user_id')->index()->comment('Связь с таблицей webapp.user');
            $table->unsignedBigInteger('account_id')->index()->comment('Связь с таблицей webapp.account');
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_dashboard');
    }
}
