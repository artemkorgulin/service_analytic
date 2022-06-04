<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableOzFeatureOptionTmp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_feature_option_tmp', function (Blueprint $table) {
            $table->unsignedBigInteger('feature_id')->index()->nullable(false)->comment('');
            $table->unsignedBigInteger('option_id')->index()->nullable(false);
            $table->unique(['feature_id', 'option_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_feature_option_tmp');
    }
}
