<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteOzPickListFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_list_goods_adds', function (Blueprint $table) {
            if (Schema::hasColumn('oz_list_goods_adds', 'conversion')) {
                $table->dropColumn('conversion');
            }

            if (Schema::hasColumn('oz_list_goods_adds', 'section')) {
                $table->dropColumn('section');
            }
        });

        Schema::table('oz_list_goods_users', function (Blueprint $table) {
            if (Schema::hasColumn('oz_list_goods_users', 'conversion')) {
                $table->dropColumn('conversion');
            }

            if (Schema::hasColumn('oz_list_goods_users', 'section')) {
                $table->dropColumn('section');
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
        Schema::table('oz_list_goods_adds', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_list_goods_adds', 'conversion')) {
                $table->integer('conversion');
            }

            if (!Schema::hasColumn('oz_list_goods_adds', 'section')) {
                $table->integer('section');
            }
        });

        Schema::table('oz_list_goods_users', function (Blueprint $table) {
            if (!Schema::hasColumn('oz_list_goods_users', 'conversion')) {
                $table->integer('conversion');
            }

            if (!Schema::hasColumn('oz_list_goods_users', 'section')) {
                $table->integer('section');
            }
        });
    }
}
