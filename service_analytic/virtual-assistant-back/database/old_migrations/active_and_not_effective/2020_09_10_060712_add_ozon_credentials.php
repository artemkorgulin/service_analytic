<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOzonCredentials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ozon_client_id', 128)->default('');
            $table->string('ozon_api_key', 128)->default('');
            $table->string('api_token', 80)->unique()->nullable()
                ->default(null)->after('password');
            $table->string('login', 128)->default('')
                ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'ozon_client_id'))
        {
            Schema::table('users', function (Blueprint $table)
            {
                $table->dropColumn('ozon_client_id');
            });
        }

        if (Schema::hasColumn('users', 'ozon_api_key'))
        {
            Schema::table('users', function (Blueprint $table)
            {
                $table->dropColumn('ozon_api_key');
            });
        }

        if (Schema::hasColumn('users', 'api_token'))
        {
            Schema::table('users', function (Blueprint $table)
            {
                $table->dropColumn('api_token');
            });
        }
    }
}
