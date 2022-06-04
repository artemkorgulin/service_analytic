<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE notification_templates
                             SET template = REPLACE(template, '//tariffs', '/tariffs')
                             WHERE template LIKE '%//tariffs%'");

        DB::statement("UPDATE notifications
                             SET message = REPLACE(message, '//tariffs', '/tariffs')
                             WHERE message LIKE '%//tariffs%'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
