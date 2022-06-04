<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUsersForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('password_resets');
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign('accounts_user_id_foreign');
        });
        Schema::table('v2_products', function (Blueprint $table) {
            $table->dropForeign('v2_products_user_id_foreign');
        });
        Schema::table('v2_temporary_products', function (Blueprint $table) {
            $table->dropForeign('v2_temporary_products_user_id_foreign');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('user_id')->on('users')->references('id');
        });
        Schema::table('v2_products', function (Blueprint $table) {
            $table->foreign('user_id')->on('users')->references('id');
        });
        Schema::table('v2_temporary_products', function (Blueprint $table) {
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_id');

            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->nullable()->index('password_resets_email_index');
            $table->string('token')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::enableForeignKeyConstraints();
    }
}
