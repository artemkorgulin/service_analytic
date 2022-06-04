<?php

use App\Models\OzDataCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OzonCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('ozon_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        OzDataCategory::create(['id' => 1, 'name' => 'одежда, обувь и аксессуары']);
        OzDataCategory::create(['id' => 2, 'name' => 'электроника']);
        OzDataCategory::create(['id' => 3, 'name' => 'дом и сад']);
        OzDataCategory::create(['id' => 4, 'name' => 'детские товары']);
        OzDataCategory::create(['id' => 5, 'name' => 'красота и здоровье']);
        OzDataCategory::create(['id' => 6, 'name' => 'бытовая техника']);
        OzDataCategory::create(['id' => 7, 'name' => 'спортивные товары']);
        OzDataCategory::create(['id' => 8, 'name' => 'строительство и ремонт']);
        OzDataCategory::create(['id' => 9, 'name' => 'продукты питания']);
        OzDataCategory::create(['id' => 10, 'name' => 'товары для животных']);
        OzDataCategory::create(['id' => 11, 'name' => 'аптека']);
        OzDataCategory::create(['id' => 12, 'name' => 'книги']);
        OzDataCategory::create(['id' => 13, 'name' => 'туризм, рыбалка, охота']);
        OzDataCategory::create(['id' => 14, 'name' => 'автотовары']);
        OzDataCategory::create(['id' => 15, 'name' => 'мебель']);
        OzDataCategory::create(['id' => 16, 'name' => 'хобби и творчество']);
        OzDataCategory::create(['id' => 17, 'name' => 'ювелирные украшения']);
        OzDataCategory::create(['id' => 18, 'name' => 'музыка и видео']);
        OzDataCategory::create(['id' => 19, 'name' => 'канцелярские товары']);
        OzDataCategory::create(['id' => 20, 'name' => 'товары для взрослых']);
        OzDataCategory::create(['id' => 21, 'name' => 'антиквариат и коллекционирование']);
        OzDataCategory::create(['id' => 22, 'name' => 'цифровые товары']);
        OzDataCategory::create(['id' => 23, 'name' => 'бытовая химия']);
        OzDataCategory::create(['id' => 24, 'name' => 'всё для игр']);
        OzDataCategory::create(['id' => 25, 'name' => 'алкоголь']);
        OzDataCategory::create(['id' => 26, 'name' => 'автомобили и мотоциклы']);
    }
}
