<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('login', 128)->nullable();
            $table->string('email')->nullable()->unique('users_email_unique');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('api_token', 80)->nullable()->unique('users_api_token_unique');
            $table->rememberToken();
            $table->string('ozon_client_id', 128)->nullable();
            $table->string('ozon_api_key', 128)->nullable();
            $table->string('verification_token', 60)->nullable();
            $table->boolean('subscription')->default(0);
            $table->string('ozon_supplier_id')->nullable();
            $table->string('ozon_supplier_api_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
