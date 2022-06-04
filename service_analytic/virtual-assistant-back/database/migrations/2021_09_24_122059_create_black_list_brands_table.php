<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlackListBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('black_list_brands', function (Blueprint $table) {
            $table->id();
            $table->string('partner')->index()->nullable(false)->comment('Партнер');
            $table->string('brand')->unique()->index()->nullable(false)->comment('Бренд');
            $table->string('manager')->index()->nullable()->comment('Менеджер');
            $table->string('status',50)->index()->nullable()->comment('Статус номенклатуры');
            $table->boolean('wildberries')->default(false)->index()->nullable()->comment('Статус в Wildberries');
            $table->boolean('ozon')->default(false)->index()->nullable()->comment('Статус в Ozon');
            $table->boolean('amazon')->default(false)->index()->nullable()->comment('Статус в Amazon');
            $table->softDeletes();
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
        Schema::dropIfExists('black_list_brands');
    }
}
