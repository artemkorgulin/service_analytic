<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToAccounts extends Migration
{

    const UniqueKeyName = 'platform_id_client_id_api_key_deleted_at_unique';


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign('accounts_platform_id_foreign');
            $table->dropUnique(['platform_id', 'platform_client_id', 'platform_api_key']);
            $table->softDeletes();
            $table->unique(['platform_id', 'platform_client_id', 'platform_api_key', 'deleted_at'], self::UniqueKeyName);
            $table->foreign('platform_id')->on('platforms')->references('id')->onDelete('restrict');
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
            $table->dropForeign('accounts_platform_id_foreign');
            $table->dropUnique(self::UniqueKeyName);
            $table->dropSoftDeletes();
            $table->unique(['platform_id', 'platform_client_id', 'platform_api_key']);
            $table->foreign('platform_id')->on('platforms')->references('id')->onDelete('restrict');
        });
    }
}
