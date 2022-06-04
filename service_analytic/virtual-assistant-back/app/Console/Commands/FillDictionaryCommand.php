<?php

namespace App\Console\Commands;

use App\Classes\Parser\Api;
use App\Models\WbCategory;
use App\Models\WbGuideProductCharacteristics;
use Illuminate\Console\Command;
use stdClass;

class FillDictionaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbdictionary:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $api;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Api $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //перебираем все категории товаров, которые наши. Для каждого типа вызываем метод api(Серега должен сделать метод)
        //получаем id категории в wb. По этому id получаем топ 100
        //перебираем топ 100 и для каждого получаем через api товар и сохраняем характеристики


        //есть ли у возвращаемого из апи товара позиция с списке? нужна
        //топ 1000 должна работать в апи

        $categorys = WbCategory::all();
        foreach ($categorys as $category) {
            //берем топ 100 продуктов для категории и обрабатываем каждый

            $weId = $this->api->getWbParseCategory($category->name);
            if ($weId) {
                $products = $this->api->getWbTopProducts($weId);
                $topPosition = 1;
                $productParameters = [];
                foreach ($products as $product) {
                    $parseProductData = $this->api->getParsingProduct($product->vendor_code);
                    if ($parseProductData) {
                        $this->getParamsFromJson($category->id, $parseProductData, $productParameters);
                    }
                    $topPosition++;
                }
                $this->syncParamsToBd($category->id, $productParameters);
            }
        }


        return 0;
    }

    /**
     * Получить массив параметров из спаршенных данных.
     *
     * @param  int  $categoryId
     * @param  stdClass  $product
     * @param  array  $productParameters
     * @return array
     */
    private function getParamsFromJson(int $categoryId, stdClass $product, array &$productParameters): array
    {
        if (isset($product->category)) {
            foreach ($product->category as $productCategory) {
                if ($productCategory->web_id) {
                    if (!isset($productParameters['category_main'])) {
                        $wbGuideProductCharacteristic = [];
                        $wbGuideProductCharacteristic['wb_category_id'] = $categoryId;
                        $wbGuideProductCharacteristic['characteristic_name'] = 'category_main';
                        $wbGuideProductCharacteristic['use_frequency'] = 0;
                        $wbGuideProductCharacteristic['rating_sum'] = 0;

                        $productParameters['category_main'] = $wbGuideProductCharacteristic;
                    }
                    $productParameters['category_main']['use_frequency']++;
                    $productParameters['category_main']['rating_sum'] += (int) $product->position;
                }
                if ($productCategory->subject_id) {
                    if (!isset($productParameters['category_subject'])) {
                        $wbGuideProductCharacteristic = [];
                        $wbGuideProductCharacteristic['wb_category_id'] = $categoryId;
                        $wbGuideProductCharacteristic['characteristic_name'] = 'category_subject';
                        $wbGuideProductCharacteristic['use_frequency'] = 0;
                        $wbGuideProductCharacteristic['rating_sum'] = 0;

                        $productParameters['category_subject'] = $wbGuideProductCharacteristic;
                    }
                    $productParameters['category_subject']['use_frequency']++;
                    $productParameters['category_subject']['rating_sum'] += (int) $product->position;
                }
            }
        }

        if (isset($product->brand[0])) {
            if (!isset($productParameters['brand'])) {
                $wbGuideProductCharacteristic = [];
                $wbGuideProductCharacteristic['wb_category_id'] = $categoryId;
                $wbGuideProductCharacteristic['characteristic_name'] = 'brand';
                $wbGuideProductCharacteristic['use_frequency'] = 0;
                $wbGuideProductCharacteristic['rating_sum'] = 0;

                $productParameters['brand'] = $wbGuideProductCharacteristic;
            }
            $productParameters['brand']['use_frequency']++;
            $productParameters['brand']['rating_sum'] += (int) $product->position;
        }

        if (isset($product->purpose[0])) {
            if (!isset($productParameters['purpose'])) {
                $wbGuideProductCharacteristic = [];
                $wbGuideProductCharacteristic['wb_category_id'] = $categoryId;
                $wbGuideProductCharacteristic['characteristic_name'] = 'purpose';
                $wbGuideProductCharacteristic['use_frequency'] = 0;
                $wbGuideProductCharacteristic['rating_sum'] = 0;

                $productParameters['purpose'] = $wbGuideProductCharacteristic;
            }
            $productParameters['purpose']['use_frequency']++;
            $productParameters['purpose']['rating_sum'] += (int) $product->position;
        }

        return $productParameters;
    }

    /**
     * Сохранить в бд параметры.
     *
     * @param  int  $categoryId
     * @param  array  $productParameters
     */
    private function syncParamsToBd(int $categoryId, array $productParameters): void
    {
        $paramsFromBd = WbGuideProductCharacteristics::where('wb_category_id',
            $categoryId)->get()->keyBy('characteristic_name');

        foreach ($productParameters as $productParameter) {
            if (!$paramsFromBd->has($productParameter['characteristic_name'])) {
                WbGuideProductCharacteristics::create([
                    'wb_category_id' => $categoryId,
                    'characteristic_name' => $productParameter['characteristic_name'],
                    'use_frequency' => $productParameter['use_frequency'],
                    'output_position' => intdiv($productParameter['rating_sum'], $productParameter['use_frequency']),
                ]);
            } else {
                if ($paramsFromBd[$productParameter['characteristic_name']]->use_frequency != $productParameter['use_frequency']) {
                    $paramsFromBd[$productParameter['characteristic_name']]->use_frequency = $productParameter['use_frequency'];
                    $paramsFromBd[$productParameter['characteristic_name']]->save();
                }
                if ($paramsFromBd[$productParameter['characteristic_name']]->output_position != intdiv($productParameter['rating_sum'],
                        $productParameter['use_frequency'])) {
                    $paramsFromBd[$productParameter['characteristic_name']]->output_position = intdiv($productParameter['rating_sum'],
                        $productParameter['use_frequency']);
                    $paramsFromBd[$productParameter['characteristic_name']]->save();
                }
            }

            $paramsFromBd->pull($productParameter['characteristic_name']);
        }

        foreach ($paramsFromBd as $paramFromBd) {
            $paramFromBd->delete();
        }
    }
}
