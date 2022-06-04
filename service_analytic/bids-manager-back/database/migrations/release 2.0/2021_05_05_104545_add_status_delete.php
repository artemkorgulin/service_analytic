<?php

use App\Models\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('status_id')->default(1)->constrained('statuses')->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->foreignId('status_id')->default(1)->constrained('statuses')->onUpdate('cascade')->onDelete('restrict');
        });

        Status::query()->insert(['id' => Status::DELETED, 'code' => 'DELETED', 'name' => 'Удален в Ozon']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign('groups_status_id_foreign');
            $table->dropColumn('status_id');
        });

        Schema::table('campaign_stop_words', function (Blueprint $table) {
            $table->dropForeign('campaign_stop_words_status_id_foreign');
            $table->dropColumn('status_id');
        });

        Status::find(Status::DELETED)->delete();
    }
}
