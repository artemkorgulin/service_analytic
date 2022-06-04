<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSchemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_schemas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_id');
            $table->enum('way_code', ['email', 'sms', 'telegram', 'whatsapp']);
            $table->string('user_ip')->nullable();
            $table->string('delete_user_ip')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type_id');
            $table->foreign('type_id')->references('id')->on('notification_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_schemas');
    }
}
