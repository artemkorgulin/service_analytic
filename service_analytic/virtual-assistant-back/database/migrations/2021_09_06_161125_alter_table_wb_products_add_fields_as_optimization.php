<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductsAddFieldsAsOptimization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wb_products', function (Blueprint $table) {
            $table->decimal('optimization', 5, 2)->index()->nullable()->default(0)->after('data')->comment('Степень оптимизации товара');
            $table->string('url')->index()->nullable()->after('data')->comment('Ссылка на продукт в Wildberries');
            $table->string('price_range')->index()->nullable()->after('data')->comment('Ценовой диапазон для продукта так как у WB продукт содержит размеры');
            $table->decimal('rating', 5, 2)->index()->nullable()->after('data')->comment('Рейтинг продукта');
            $table->tinyInteger('status_id')->index()->nullable()->after('data')->comment('ID статаса продукта');
            $table->string('status')->index()->nullable()->after('data')->comment('Статус продукта');
            $table->boolean('is_advertised')->nullable()->after('data')->comment('Продукт рекламный или нет');
            $table->boolean('is_notificated')->nullable()->after('data')->comment('Продукт с нотификацией или нет');
            $table->boolean('is_test')->nullable()->after('data')->comment('Тестовый продукт или нет');
            $table->integer('count_reviews')->nullable()->after('data')->comment('Количество отзывов ');

            $table->string('dimension_unit', 10)->nullable()->default('sm')->comment('Единица размеров товара')->after('data');
            $table->integer('depth')->nullable()->default(0)->comment('Глубина товара')->after('dimension_unit');
            $table->integer('height')->nullable()->default(0)->comment('Высота товара')->after('depth');
            $table->integer('width')->nullable()->default(0)->comment('Ширина товара')->after('height');

            $table->string('weight_unit', 10)->nullable()->default('kg')->comment('Единица веса товара')->after('width');
            $table->integer('weight')->nullable()->default(0)->comment('Вес товара')->after('weight_unit');
        });

        Schema::table('oz_product_positions_history_graph', function (Blueprint $table) {
            $table->string('category')->nullable()->index()->comment('Для хранения категорий')->change();
            $table->date('date')->index()->comment('Для хранения даты')->change();
        });

        Schema::table('oz_products', function (Blueprint $table) {
            $table->string('brand')->nullable()->index()->comment('Поле для хранения названия бренда');
            $table->json('characteristics')->nullable()->after('optimization')->comment('Поле для хранения характеристик для быстрого возврата');
            $table->json('recommendations')->nullable()->after('optimization')->comment('Поле для хранения рекомендаций');
            $table->json('recommended_characteristics')->nullable()->after('optimization')->comment('Поле для хранения рекомендованных характеристик');
            $table->json('error_characteristics')->nullable()->after('optimization')->comment('Для хранения ошибок в характеристиках');
            $table->json('descriptions')->nullable()->after('optimization')->comment('Описания (json массив)');
            $table->json('images')->nullable()->after('optimization')->comment('Для хранения массива картинок');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }

}
