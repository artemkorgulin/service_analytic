<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStatusNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status', function (Blueprint $table) {
            $table->string('name')->default('');
        });

        DB::table('status')->where('status', 'ACTIVE')->update(['name' => 'Активно']);
        DB::table('status')->where('status', 'NOT_ACTIVE')->update(['name' => 'Не активно']);
        DB::table('status')->where('status', 'ARCHIVED')->update(['name' => 'В архиве']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
