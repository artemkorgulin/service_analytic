<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOzCategoryFeatureOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oz_category_feature_option', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('feature_id')->index();
            $table->unsignedBigInteger('option_id');
            $table->primary(['category_id', 'feature_id', 'option_id'], 'oz_category_feature_option_primary');

            $table->foreign('category_id')->references('id')->on('oz_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('feature_id')->references('id')->on('oz_features')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('option_id')->references('id')->on('oz_feature_options')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('oz_features', function (Blueprint $table) {
            $table->boolean('is_unique_for_category')->default(0);
        });

        DB::table('oz_features')->where('id', '=', 8229)->update(['is_unique_for_category' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oz_category_feature_option');

        Schema::table('oz_features', function (Blueprint $table) {
            $table->dropColumn(['is_unique_for_category']);
        });
    }
}
