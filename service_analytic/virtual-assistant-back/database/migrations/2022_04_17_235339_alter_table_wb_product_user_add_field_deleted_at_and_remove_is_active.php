<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWbProductUserAddFieldDeletedAtAndRemoveIsActive extends Migration
{
    protected string $table = 'wb_product_user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn($this->table, 'is_active') &&
            !Schema::hasColumn($this->table, 'deleted_at')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->softDeletes()->index()->comment('Для деактивации товара у пользователя');
            });
            $this->moveDataFromIsActiveToDeletedAt();
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn($this->table, 'is_active') &&
            Schema::hasColumn($this->table, 'deleted_at')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->boolean('is_active')->index()->comment('Для деактивации товара у пользователя');
            });
            $this->moveBackDataFromIsActiveToDeletedAt();
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }

    /**
     * For move data
     */
    private function moveDataFromIsActiveToDeletedAt()
    {
        DB::table($this->table)->where('is_active', false)
            ->update(['deleted_at' => \Carbon\Carbon::now()]);
    }


    /**
     * For move data
     */
    private function moveBackDataFromIsActiveToDeletedAt()
    {
        DB::table($this->table)->whereNull('deleted_at')
            ->update(['active' => true]);
        DB::table($this->table)->whereNotNull('deleted_at')
            ->update(['active' => false]);
    }
}
