<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('message');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('subtype_id');
            $table->timestamp('created_at');
            $table->softDeletes();

            $table->index('template_id');
            $table->foreign('template_id')->references('id')->on('notification_templates');

            $table->index('type_id');
            $table->foreign('type_id')->references('id')->on('notification_types');

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
        Schema::dropIfExists('notifications');
    }
}
