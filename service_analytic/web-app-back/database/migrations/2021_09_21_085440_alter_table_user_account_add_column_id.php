<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableUserAccountAddColumnId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_account', function (Blueprint $table) {
            $table->id();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['individual', 'legal'])->comment('Физическое, юридическое лицо');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_account', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
