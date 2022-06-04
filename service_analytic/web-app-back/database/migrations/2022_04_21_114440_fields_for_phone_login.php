<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FieldsForPhoneLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_verified')
                  ->nullable()
                  ->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('phone_verification_token', 60)->nullable();
            $table->timestamp('phone_verification_token_created_at')->nullable();
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
            $table->dropColumn('phone_verified');
            $table->dropColumn('phone_verified_at');
            $table->dropColumn('phone_verification_token');
            $table->dropColumn('phone_verification_token_created_at');
        });
    }
}
