<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusFailedModerationInTableOzProductStatuses extends Migration
{

    public string $table = 'oz_product_statuses';

    public array $insertedStatusesData = [
        ['code' => 'verified', 'name' => 'Проверен'],
        ['code' => 'moderation', 'name' => 'На модерации'],
        ['code' => 'error', 'name' => 'Ошибка'],
        ['code' => 'created', 'name' => 'Создан (локально)'],
        ['code' => 'failed_validation', 'name' => 'Ошибка валидации'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->insertedStatusesData as $insertedStatusRecord) {
            $failedModerationRecord = DB::table($this->table)->select()
                ->where(['code' => $insertedStatusRecord['code']])->first();
            if (!$failedModerationRecord) {
                DB::table($this->table)
                    ->insert(['code' => $insertedStatusRecord['code'], 'name' => $insertedStatusRecord['name']]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Напишу по-русски здесь комментарий, здесь не будет никакого кода по очень простой причине,
        // Так как содержимое таблицы oz_product_statuses везде разное. ХЗ как это так получилось, но на prod
        // не хватает 2 статусов, у меня на локалке 1, а на dev есть все. Я посчитал неправильным что-то удалять
        // или удалять все, поэтому обратной миграции не будет тут.
    }
}
