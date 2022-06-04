<?php

use Symfony\Component\Console\Output\ConsoleOutput;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Billing2022Information extends Migration
{
    public function hasAnyDataInTables()
    {
        foreach(['tariffs', 'services', 'service_prices', 'service_tariff'] as $table){
            if(DB::table($table)->count()){
                return true;
            }
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if($this->hasAnyDataInTables()) {
            $output = new ConsoleOutput();
            $output->writeln('В базе данных уже есть информация по билингу, заполнение не выполняется');
            return ;
        }

        $now = DB::raw('now()');

        DB::table('tariffs')->insert([
            'name' => 'Промо',
            'alias' => 'promo',
            'description' => '100 SKU',
            'price' => 299000,
            'visible' => 1,
            'created_at' => $now, 'updated_at' => $now
        ]);

        DB::table('services')->insert([
            'name' => 'Оптимизация карточек товаров',
            'alias' => 'semantic',
            'description' => "Удобные графики для анализа продаж и видимости товаров\n".
                             "Оптимизация карточек под требования маркетплейса\n".
                             "Поисковая SEO-оптимизация карточек\n".
                             "Рекомендации, разработанные специально для ваших товаров",
            'visible' => 1,
            'countable' => 1,
            'sellable' => 1,
            'created_at' => $now, 'updated_at' => $now
        ]);

        DB::table('services')->insert([
            'name' => 'Аналитика маркетплейсов',
            'alias' => 'analytics',
            'description' =>
                "Удобные графики и таблицы для анализа брендов, продавцов и категорий\n".
                "Понятная аналитика для отслеживания трендов на маркетплейсах\n".
                "Конкурентный анализ по поставщикам, выручке и ассортименту\n".
                "Ценовой анализ брендов и категорий",
            'visible' => 1,
            'countable' => 0,
            'sellable' => 0,
            'created_at' => $now, 'updated_at' => $now
        ]);

        DB::table('services')->insert([
            'name' => 'Реклама OZON',
            'alias' => 'ad',
            'description' =>
                "Автоматический поиск оптимальной ставки для выкупа заданного количества показов (CPM)\n".
                "Автоматический поиск оптимальной ставки для для удержания заданной стоимости заказа (CPO)\n".
                "Автоматический подбор эффективных ключевых запросов и отключение неэффективны",
            'visible' => 1,
            'countable' => 0,
            'sellable' => 0,
            'created_at' => $now, 'updated_at' => $now
        ]);

        DB::table('services')->insert([
            'name' => 'Депонирование',
            'alias' => 'depo',
            'description' =>
                "Подтверждение вашего авторства на изображения и инфографику официальным свидетельством\n".
                "Несколько простых шагов для успешного решения вопросов с поддержкой площадок\n".
                "Подготовка документов для решения споров на маркетплейсе",
            'visible' => 1,
            'countable' => 0,
            'sellable' => 0,
            'created_at' => $now, 'updated_at' => $now
        ]);

        DB::table('services')->insert([
            'name' => 'Корпоративный доступ',
            'alias' => 'corp',
            'description' =>
                "Доступ к работе сервиса до 10 человек одновременно\n".
                "Добавление сотрудников, управление доступом и ролями\n".
                "Разделение функционала и ассортимента между сотрудниками\n",
            'visible' => 1,
            'min_order_amount' => 10,
            'max_order_amount' => 10,
            'countable' => 1,
            'sellable' => 1,
            'created_at' => $now, 'updated_at' => $now
        ]);

        $this->setPriceForService('corp', 200 * 100, 0);
        $this->setPriceForService('semantic', 2990, 0);

        $this->setPriceForService('depo', 0, 0);
        $this->setPriceForService('ad', 0, 0);
        $this->setPriceForService('analytics', 0, 0);

        $this->addServiceToTariff('promo', 'semantic', 100);
        $this->addServiceToTariff('promo', 'depo', 0);
        $this->addServiceToTariff('promo', 'ad', 0);
        $this->addServiceToTariff('promo', 'analytics', 0);
    }

    private function setPriceForService($alias, $price, $amount)
    {
        $serviceId = DB::table('services')->where('alias', $alias)->value('id');
        $now = DB::raw('now()');
        DB::table('service_prices')->insert([
            'service_id' => $serviceId,
            'min_amount' => $amount,
            'price_per_item' => $price,
            'created_at' => $now, 'updated_at' => $now
        ]);

    }

    private function addServiceToTariff($tariffAlias, $serviceAlias, $amount)
    {
        $serviceId = DB::table('services')->where('alias', $serviceAlias)->value('id');
        $tariffId = DB::table('tariffs')->where('alias', $tariffAlias)->value('id');

        DB::table('service_tariff')->insert([
            'service_id' => $serviceId,
            'tariff_id' => $tariffId,
            'amount' => $amount,
        ]);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
