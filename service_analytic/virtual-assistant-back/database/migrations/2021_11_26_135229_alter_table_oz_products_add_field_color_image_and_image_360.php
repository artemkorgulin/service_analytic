<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOzProductsAddFieldColorImageAndImage360 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_products', 'color_image')) {
                $table->string('color_image')
                    ->index()
                    ->nullable()
                    ->after('photo_url')
                    ->comment('Store color image');
            }
            if (!Schema::hasColumn('oz_products', 'images360')) {
                $table->json('images360')
                    ->nullable()
                    ->after('images')
                    ->comment('Store images for 360 views');
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
        Schema::table('oz_products', function (Blueprint $table) {
            if (Schema::hasColumn('oz_products', 'color_image')) {
                $table->dropColumn('color_image');
            }
            if (Schema::hasColumn('oz_products', 'images360')) {
                $table->dropColumn('images360');
            }
        });
    }
}
