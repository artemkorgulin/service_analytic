<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableEscrowCertificates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escrow_certificates', function (Blueprint $table) {
            $table->string('copyright_holder');
            $table->string('full_name');
            $table->string('email');
            $table->bigInteger('nmid')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escrow_certificates', function (Blueprint $table) {
            $table->dropColumn('copyright_holder');
            $table->dropColumn('full_name');
            $table->dropColumn('email');
            $table->dropColumn('nmid');
        });
    }
}
