<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzCategoresAddIndexToNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_categories', function (Blueprint $table) {
            if (Schema::hasColumn('oz_categories', 'name')) {
                $table->index(['name']);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_categories', function (Blueprint $table) {
            if (Schema::hasColumn('oz_categories', 'name')) {
                try {
                    $table->dropIndex(['name']);
                } catch (\Exception $e) {
                    print($e->getMessage());
                }
            }
        });
    }
}
