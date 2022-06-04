<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePlatformSemanticRufago extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('platform_semantic_rufago', function (Blueprint $table) {
            $table->mediumText('search_response')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('platform_semantic_rufago', function (Blueprint $table) {
            $table->string('search_response')->default('')->change();
        });
    }
}
