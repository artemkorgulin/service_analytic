<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ozon_categories', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('id');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
        Schema::table('root_queries', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('id');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
        Schema::table('search_queries', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('id');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ozon_categories', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
        Schema::table('root_queries', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
