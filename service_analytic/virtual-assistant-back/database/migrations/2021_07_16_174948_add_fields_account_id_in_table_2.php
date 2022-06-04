<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsAccountIdInTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_products', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->index()->after('user_id');
            }
        });

        Schema::table('oz_temporary_products', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_temporary_products', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->index()->after('user_id');
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

    }
}
