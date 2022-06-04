<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUniquckInProductsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oz_products', function (Blueprint $table) {
            $table->unique(['external_id']);
        });
        Schema::table('oz_temporary_products', function (Blueprint $table) {
            $table->unique(['external_id']);
        });
        Schema::table('wb_products', function (Blueprint $table) {
            $table->unique(['imt_id']);
        });
        Schema::table('wb_temporary_products', function (Blueprint $table) {
            $table->unique(['imt_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->deleteIndexIfExists('oz_products', 'external_id');
        $this->deleteIndexIfExists('oz_temporary_products', 'external_id');
        $this->deleteIndexIfExists('wb_products', 'imt_id');
        $this->deleteIndexIfExists('wb_temporary_products', 'imt_id');
    }

    public function deleteIndexIfExists($tableName, $indexName)
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails($tableName);
            if ($doctrineTable->hasIndex($indexName)) {
                $table->dropIndex($indexName);
            }
            $uniqueIndex = $tableName . '_' . $indexName . '_unique';
            if ($doctrineTable->hasIndex($uniqueIndex)) {
                $table->dropIndex($uniqueIndex);
            }
        });
    }
}
