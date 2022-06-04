<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('enable_email_notifications');
            $table->string('ozon_client_id', 128)->default('');
            $table->string('ozon_client_api_key', 128)->default('');
            $table->string('ozon_supplier_id', 128)->default('');
            $table->string('ozon_supplier_api_key', 128)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('enable_email_notifications');
            $table->dropColumn('ozon_client_id');
            $table->dropColumn('ozon_api_key');
            $table->dropColumn('ozon_supplier_id');
            $table->dropColumn('ozon_supplier_api_key');
        });
    }
}
