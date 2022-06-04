<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWbProductStatusesDropProductStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wb_product_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->json('marketplace_equivalents')->nullable();
        });

        $this->insertDataToTable('wb_product_statuses');

        Schema::dropIfExists('product_statuses');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_product_statuses');

        Schema::create('product_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->json('marketplace_equivalents')->nullable();
        });

        $this->insertDataToTable('product_statuses');
    }

    private function insertDataToTable(string $tableName)
    {
        DB::table($tableName)->insert([
            [
                'id' => 1,
                'code' => 'verified',
                'name' => 'Проверен',
                'marketplace_equivalents' => '{"ozon": ["processed"], "wildberries": []}'
            ],
            [
                'id' => 2,
                'code' => 'moderation',
                'name' => 'На модерации',
                'marketplace_equivalents' => '{"ozon": ["processing", "moderating"], "wildberries": []}'
            ],
            [
                'id' => 3,
                'code' => 'error',
                'name' => 'Ошибка',
                'marketplace_equivalents' => '{"ozon": ["failed_moderation", "failed_validation", "failed"], "wildberries": []}'
            ],
            [
                'id' => 4,
                'code' => 'created',
                'name' => 'Создан',
                'marketplace_equivalents' => null
            ],
            [
                'id' => 5,
                'code' => 'modified',
                'name' => 'Отредактирован локально',
                'marketplace_equivalents' => null
            ]
        ]);
    }
}
