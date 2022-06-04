<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('client_id_seller')->nullable();
            $table->string('api_key')->nullable();
            $table->integer('client_id_performance')->nullable();
            $table->string('client_secret')->nullable();
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
            $table->dropColumn('client_id_seller');
            $table->dropColumn('api_key');
            $table->dropColumn('client_id_performance');
            $table->dropColumn('client_secret');
        });
    }
}
