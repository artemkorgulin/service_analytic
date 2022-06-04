<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzCategoriesAddSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_categories', 'settings')) {
                $table->json('settings')
                    ->nullable()
                    ->after('count_features')
                    ->comment('Данное поле содержит установки для типа товаров (конечные ветки в таблице категорий)');
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_categories', function (Blueprint $table) {
            if (Schema::hasColumn('oz_categories', 'settings')) {
                $table->dropColumn('settings');
            }
        });

    }
}
