<?php

use AnalyticPlatform\LaravelHelpers\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOldUsersAndOldAccountsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (MigrationHelper::hasForeign('cron_uuid_phrase', 'cron_uuid_phrase_account_id_foreign')) {
            Schema::table('cron_uuid_phrase', function (Blueprint $table) {
                $table->dropForeign('cron_uuid_phrase_account_id_foreign');
            });
        }

        Schema::dropIfExists('old_accounts');
        Schema::dropIfExists('old_users');
        Schema::dropIfExists('users');
        Schema::dropIfExists('api_ad_object');
        Schema::dropIfExists('api_campaign');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('api_campaign', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('status_id');
            $table->json('adv_object_type')->nullable();
            $table->timestamps();
        });

        Schema::create('api_ad_object', function (Blueprint $table) {
            $table->id();
            $table->integer('object_id');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('old_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('old_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('client_id');
            $table->string('client_secret_id');
            $table->timestamps();
        });
    }
}
