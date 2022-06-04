<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToTableOzFeatureOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_feature_options', function (Blueprint $table) {
            $table->text('value')->comment('Для хранения значений')->change();
        });

        \DB::statement('ALTER TABLE oz_feature_options ADD FULLTEXT search(value)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oz_feature_options', function (Blueprint $table) {

        });
    }
}
