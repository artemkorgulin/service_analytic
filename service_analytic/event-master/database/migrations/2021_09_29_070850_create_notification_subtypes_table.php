<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSubtypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_subtypes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->unsignedBigInteger('type_id');
            $table->string('code');
            $table->string('name');

            $table->index('type_id');
            $table->foreign('type_id')->references('id')->on('notification_types');
        });

        DB::table('notification_subtypes')->insert([
            ['id' => 1, 'type_id' => 1, 'code' => 'technical.system_update', 'name' => 'Производится обновление системы'],
            ['id' => 2, 'type_id' => 1, 'code' => 'technical.functional_changes', 'name' => 'Произошли изменения функционала'],
            ['id' => 3, 'type_id' => 1, 'code' => 'technical.new_module', 'name' => 'Добавлен новый модуль'],

            ['id' => 4, 'type_id' => 7, 'code' => 'technical.product_card_editing_block', 'name' => 'Недоступно редактирование карточки товара'],
            ['id' => 5, 'type_id' => 7, 'code' => 'technical.ends_promotional_period', 'name' => 'Завершается промо-период использования'],
            ['id' => 6, 'type_id' => 7, 'code' => 'technical.transferred_free_tariff', 'name' => 'Произведен перевод на бесплатный тариф'],
            ['id' => 7, 'type_id' => 7, 'code' => 'technical.user_block_cause', 'name' => 'Пользователь заблокирован по причине'],
            ['id' => 8, 'type_id' => 7, 'code' => 'technical.subscription_end_days', 'name' => 'До окончания подписки осталось'],

            ['id' => 9, 'type_id' => 2, 'code' => 'billing.invoice_generated', 'name' => 'Сформирован счет №'],
            ['id' => 10, 'type_id' => 2, 'code' => 'billing.invoice_generated', 'name' => 'Сформирован Акт выполненных работ'],
            ['id' => 11, 'type_id' => 2, 'code' => 'billing.invoice_status_changes', 'name' => 'Изменился статус счета №'],

            ['id' => 12, 'type_id' => 3, 'code' => 'card_product.curd_upload', 'name' => 'Карточки/карточка товара загружены в систему'],
            ['id' => 13, 'type_id' => 3, 'code' => 'card_product.necessary_fill_fields', 'name' => 'Необходимо заполнение полей карточки товара(указание полей)'],
            ['id' => 14, 'type_id' => 3, 'code' => 'card_product.curd_rating_change', 'name' => 'Произошло изменении рейтинга карточки'],
            ['id' => 15, 'type_id' => 3, 'code' => 'card_product.unanswered_questions', 'name' => 'Есть неотвеченные вопросы'],
            ['id' => 16, 'type_id' => 3, 'code' => 'card_product.uncommented_reviews', 'name' => 'Есть не прокомментированные отзывы'],
            ['id' => 17, 'type_id' => 3, 'code' => 'card_product.middle_rating_change_top10', 'name' => 'Произошло изменении среднего рейтинга в ТОП 10 категории'],
            ['id' => 18, 'type_id' => 3, 'code' => 'card_product.decrease_below_15day', 'name' => 'Произошло снижении запасов товара ниже 15-ти дневного уровня'],

            ['id' => 19, 'type_id' => 4, 'code' => 'optimization.card_product_not_optimize', 'name' => 'Карточка товара не оптимизирована'],
            ['id' => 20, 'type_id' => 4, 'code' => 'optimization.change_optimize_level_card', 'name' => 'Произошло изменении уровня оптимизации карточки'],
            ['id' => 21, 'type_id' => 4, 'code' => 'optimization.yesterday_decline_views', 'name' => 'Произошло значительное снижение просмотров на вчерашний день, относительно среднего за 10 последних'],

            ['id' => 22, 'type_id' => 5, 'code' => 'advertising_companies.necessary_top_up_balance', 'name' => 'Необходимо пополнить баланс рекламной кампании'],
            ['id' => 23, 'type_id' => 5, 'code' => 'advertising_companies.advertising_campaign_completed', 'name' => 'Рекламная кампания завершена'],

            ['id' => 24, 'type_id' => 6, 'code' => 'price_machine.change_price_substitute_products', 'name' => 'Произошло изменении цены на товары конкурента / товары заменители'],
            ['id' => 25, 'type_id' => 6, 'code' => 'price_machine.product_promotion', 'name' => 'Произошло изменение цены товара, товар участвует в акции'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_subtypes');
    }
}
