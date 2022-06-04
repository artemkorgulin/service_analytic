<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnAndTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_histories');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tariff_activity_ids')) {
                $table->dropColumn('tariff_activity_ids');
            }
        });

        Schema::table('tariff_activity', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'payment_id')) {
                $table->dropColumn('payment_id');
            }
        });

        DB::table('tariffs')->whereIn('tariff_id', [1, 2, 3, 4])->update(['active' => 1]);

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            //
        });
        Schema::create('subscription_histories', function (Blueprint $table) {
            //
        });

        Schema::table('tariff_activity', function (Blueprint $table) {
            $table->json('tariff_activity_ids')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')->nullable();
        });

        DB::table('tariffs')->whereIn('tariff_id', [1,2,3,4])->update( ['active' => 1]);
    }
}
