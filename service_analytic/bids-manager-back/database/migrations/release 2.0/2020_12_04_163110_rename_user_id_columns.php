<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUserIdColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign', function(Blueprint $table) {
//            $table->dropForeign('campaign_user_id_foreign');
            $table->renameColumn('user_id', 'account_id');
        });

        Schema::table('good', function(Blueprint $table) {
            $table->integer('account_id')->nullable();
        });

        Schema::table('keyword', function(Blueprint $table) {
            $table->integer('account_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign', function(Blueprint $table) {
            $table->renameColumn('account_id', 'user_id');
        });

        Schema::table('good', function(Blueprint $table) {
            $table->dropColumn('account_id');
        });

        Schema::table('keyword', function(Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
}
