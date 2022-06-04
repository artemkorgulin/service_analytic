<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            DB::statement('ALTER TABLE accounts CHANGE COLUMN created_at created_at TIMESTAMP NULL DEFAULT NULL AFTER id;');
            DB::statement('ALTER TABLE accounts CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;');

            $table->string('seller_client_id')->nullable()->after('client_secret_id');
            $table->string('seller_api_key')->nullable()->after('seller_client_id');

            $table->renameColumn('client_id', 'perfomance_client_id');
            $table->renameColumn('client_secret_id', 'perfomance_client_secret');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->renameColumn('perfomance_client_id', 'client_id');
            $table->renameColumn('perfomance_client_secret', 'client_secret_id');
            $table->dropColumn('seller_client_id');
            $table->dropColumn('seller_api_key');
        });
    }
}
