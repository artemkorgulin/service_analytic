<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateTableBarcodes extends Migration
{
    // created tables
    protected $tableWbBarcodes = 'wb_barcodes';
    protected $tableWbNomenclatures = 'wb_nomenclatures';
    protected $tableWbBarcodeStocks = 'wb_barcode_stocks';

    // altered tables
    protected $tableWbProducts = 'wb_products';
    protected $tableWbTemporaryProducts = 'wb_temporary_products';

    // in stock quantity field name
    protected $inStockFieldName = 'quantity';


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tableWbBarcodes)) {
            Schema::drop($this->tableWbBarcodes);
        }
        Schema::create($this->tableWbBarcodes, function (Blueprint $table) {
            $table->bigInteger('barcode')->unique()->index()->comment('Баркод');
            $table->string('subject')->index()->comment('Видимо объект');
            $table->string('brand')->index()->comment('Бренд');
            $table->string('name')->index()->comment('Наименование');
            $table->string('size')->index()->comment('Размер');
            $table->json('barcodes')->comment('Баркоды');
            $table->string('article')->index()->comment('Артикул');
            $table->boolean('used')->index()->default(true)->comment('Использован ли баркод (если его сами создали)');
            $table->integer('quantity')->index()->default(0)->comment('Есть ли в наличии на складах при последней проверке (количество)');
            $table->timestamps();
        });

        if (Schema::hasTable($this->tableWbBarcodeStocks)) {
            Schema::drop($this->tableWbBarcodeStocks);
        }
        Schema::create($this->tableWbBarcodeStocks, function (Blueprint $table) {
            $table->bigInteger('barcode')->index()->comment('Баркод');
            $table->date('check_date')->index()->comment('Дата проверки');
            $table->integer('quantity')->index()->default(0)->comment('Остаток');
            $table->string('warehouse_name')->index()->comment('Наименование склада');
            $table->integer('warehouse_id')->index()->comment('Id склада');
            // Add index for unique
            $table->unique('barcode', 'check_date');
        });

        // for nomenclatures create extra fields
        if (!Schema::hasColumn($this->tableWbNomenclatures, 'quantity')) {
            Schema::table($this->tableWbNomenclatures, function (Blueprint $table) {
                $table->integer('quantity')->index()->default(0)
                    ->comment('Есть ли в наличии на складах (по сути есть или нет)')
                    ->after('promocode');
            });
        }

        if (!Schema::hasColumn($this->tableWbProducts, 'quantity')) {
            Schema::table($this->tableWbProducts, function (Blueprint $table) {
                $table->integer('quantity')->index()->default(0)
                    ->comment('Есть ли в наличии на складах при последней проверке (количество)')
                    ->after('search_optimization');
            });
        }

        if (!Schema::hasColumn($this->tableWbTemporaryProducts, 'quantity')) {
            Schema::table($this->tableWbTemporaryProducts, function (Blueprint $table) {
                $table->integer('quantity')->index()->default(0)
                    ->comment('Есть ли в наличии на складах при последней проверке (количество)')
                    ->after('url');
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableWbBarcodes);

        Schema::dropIfExists($this->tableWbBarcodes);
        // no need must to check
        if (Schema::hasColumn($this->tableWbProducts, 'quantity')) {
            Schema::dropColumns($this->tableWbProducts, ['quantity']);
        }
        if (Schema::hasColumn($this->tableWbProducts, 'quantity')) {
            Schema::dropColumns($this->tableWbProducts, ['quantity']);
        }
        if (Schema::hasColumn($this->tableWbTemporaryProducts, 'quantity')) {
            Schema::dropColumns($this->tableWbTemporaryProducts, ['quantity']);
        }
    }
}
