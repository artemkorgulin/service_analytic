<?php

use App\Models\OzDataSellerCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OzonSellerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('ozon_seller_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        OzDataSellerCategory::create(['name' => 'Автомобили', 'ozon_id' => '92120918', 'ozon_category_id' => 26]);
        OzDataSellerCategory::create(['name' => 'Обувь', 'ozon_id' => '15621032', 'ozon_category_id' => 1]);
        OzDataSellerCategory::create(['name' => 'Одежда', 'ozon_id' => '15621031', 'ozon_category_id' => 1]);
        OzDataSellerCategory::create(['name' => 'Галантерея и украшения', 'ozon_id' => '17027493', 'ozon_category_id' => 1]);
        OzDataSellerCategory::create(['name' => 'Аптека', 'ozon_id' => '52265716', 'ozon_category_id' => 11]);
        OzDataSellerCategory::create(['name' => 'Ювелирное изделие', 'ozon_id' => '76902590', 'ozon_category_id' => 17]);
        OzDataSellerCategory::create(['name' => 'Печатные издания', 'ozon_id' => '29', 'ozon_category_id' => 12]);
        OzDataSellerCategory::create(['name' => 'Ремонт и строительство', 'ozon_id' => '17027482', 'ozon_category_id' => 8]);
        OzDataSellerCategory::create(['name' => 'Спорт и отдых', 'ozon_id' => '17027491', 'ozon_category_id' => 7]);
        OzDataSellerCategory::create(['name' => 'Фермерское хозяйство', 'ozon_id' => '88976462', 'ozon_category_id' => 10]);
        OzDataSellerCategory::create(['name' => 'Зоотовары', 'ozon_id' => '17027487', 'ozon_category_id' => 10]);
        OzDataSellerCategory::create(['name' => 'Бытовая техника', 'ozon_id' => '17027486', 'ozon_category_id' => 6]);
        OzDataSellerCategory::create(['name' => 'Автотовары', 'ozon_id' => '17027495', 'ozon_category_id' => 14]);
        OzDataSellerCategory::create(['name' => 'Антиквариат и коллекционирование', 'ozon_id' => '17027490', 'ozon_category_id' => 21]);
        OzDataSellerCategory::create(['name' => 'Канцелярия', 'ozon_id' => '17027492', 'ozon_category_id' => 19]);
        OzDataSellerCategory::create(['name' => 'Детские товары', 'ozon_id' => '17027488', 'ozon_category_id' => 4]);
        OzDataSellerCategory::create(['name' => 'Бытовая химия', 'ozon_id' => '75021418', 'ozon_category_id' => 23]);
        OzDataSellerCategory::create(['name' => 'Дом', 'ozon_id' => '17027494', 'ozon_category_id' => 3]);
        OzDataSellerCategory::create(['name' => 'Мебель', 'ozon_id' => '17027915', 'ozon_category_id' => 15]);
        OzDataSellerCategory::create(['name' => 'Музыкальные инструменты', 'ozon_id' => '92130764', 'ozon_category_id' => 16]);
        OzDataSellerCategory::create(['name' => 'Хобби и творчество', 'ozon_id' => '17027485', 'ozon_category_id' => 16]);
        OzDataSellerCategory::create(['name' => 'Электроника', 'ozon_id' => '15621042', 'ozon_category_id' => 2]);
        OzDataSellerCategory::create(['name' => 'Красота и здоровье', 'ozon_id' => '17027489', 'ozon_category_id' => 5]);
        OzDataSellerCategory::create(['name' => 'Антикварные издания', 'ozon_id' => '31', 'ozon_category_id' => 12]);
        OzDataSellerCategory::create(['name' => 'Продукты питания', 'ozon_id' => '17027496', 'ozon_category_id' => 9]);
        OzDataSellerCategory::create(['name' => '18+', 'ozon_id' => '17027484', 'ozon_category_id' => 20]);
        OzDataSellerCategory::create(['name' => 'Продукт медиа', 'ozon_id' => '99999999', 'ozon_category_id' => 18]);
        OzDataSellerCategory::create(['name' => 'Личная гигиена', 'ozon_id' => '92408995', 'ozon_category_id' => 11]);
    }
}
