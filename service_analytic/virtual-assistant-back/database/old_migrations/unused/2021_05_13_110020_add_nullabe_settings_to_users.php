<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullabeSettingsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ozon_client_id', 128)->nullable()->change();
            $table->string('ozon_api_key', 128)->nullable()->change();
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
            $table->string('ozon_client_id', 128)->nullable(false)->default('')->change();
            $table->string('ozon_api_key', 128)->nullable(false)->default('')->change();
        });
    }
}
