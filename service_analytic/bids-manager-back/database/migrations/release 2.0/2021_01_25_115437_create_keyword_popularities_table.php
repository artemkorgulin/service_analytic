<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordPopularitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyword_popularities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('keyword_id')->constrained('keywords')->onUpdate('cascade')->onDelete('cascade');
            $table->date('date')->index('keyword_popularities_date');
            $table->integer('popularity')->nullable()->unsigned();

            $table->index(['keyword_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keyword_popularities');
    }
}
