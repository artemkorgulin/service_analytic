<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->text('template');
            $table->enum('lang', ['ru', 'en']);
            $table->unsignedBigInteger('subtype_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index('subtype_id');
            $table->foreign('subtype_id')->references('id')->on('notification_subtypes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_templates');
    }
}
