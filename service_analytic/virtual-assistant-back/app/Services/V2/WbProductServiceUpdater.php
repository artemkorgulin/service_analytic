<?php

namespace App\Services\V2;

use App\Classes\Helper;
use App\Models\NotShowingWbCharacteristics;
use App\Models\WbDirectory;
use App\Models\WbDirectoryItem;
use App\Models\WbProduct;
use App\Models\YesNoWbCharacteristics;
use App\Services\Wildberries\WbSearchService;
use AnalyticPlatform\LaravelHelpers\Exceptions\EntityNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Класс для актуализации информации о товаре
 * Class ProductServiceUpdater
 * @package App\Services\V2
 */
class WbProductServiceUpdater
{
    /**
     * Модель товара
     * @var WbProduct
     */
    protected $model;

    protected static $platform_client_id;

    protected static $platform_api_key;

    /**
     * ProductServiceUpdater constructor.
     * @param int|WbProduct $product - id товара или объект OzProduct
     * @throws Exception
     */
    public function __construct($product, $account_id = null)
    {
        if (is_int($product)) {
            $this->model = WbProduct::find($product);
        } elseif ($product instanceof WbProduct) {
            $this->model = $product;
        } else {
            throw new Exception('Wrong argument type');
        }

        if ($this->model === null) {
            throw new EntityNotFoundException('Товар не найден');
        }
    }

    /**
     * Подсчёт степени контентной оптимизации карточки
     * @return float
     */
    public static function calculateContentOptimization($model): float
    {
        $data = Helper::getUsableData($model);
        $categoryCharacteristics = $data->addin;
        $categoryCharacteristicArray = [];
        $excludeCharacteristics = ['Код ролика на YouTube', 'Аннотация', 'Изображения', '3D-изображение', 'Название', 'Наименование', 'Ключевые слова'];
        foreach ($categoryCharacteristics as $characteristic) {
            if (!in_array($characteristic->type, $excludeCharacteristics)) {
                $categoryCharacteristicArray[] = $characteristic->type;
            }
        }
        $productCharacteristics = $data->addin;
        $productCharacteristicsArray = [];
        foreach ($productCharacteristics as $characteristic) {
            if (isset($characteristic->params) && sizeof($characteristic->params)) {
                $productCharacteristicsArray[] = $characteristic->type;
            }
        }
        $productCharacteristicsArray = array_unique($productCharacteristicsArray);
        $filledCharacteristicsArray = array_intersect($categoryCharacteristicArray, $productCharacteristicsArray);
        $countFilledCharacteristics = count($filledCharacteristicsArray);
        $optimization = $countFilledCharacteristics / count($productCharacteristicsArray) * 100;
        $percent = number_format($optimization, 2, '.', '');
        if ($percent > 100) {
            $percent = 100;
        }
        return $percent;
    }

    /**
     * Подсчёт степени поисковой оптимизации карточки
     * @return float
     */
    public static function calculateSearchOptimization($model): float
    {
        $keywords = $model->keywords()->get()->pluck('name');
        $keywordsCount = count($keywords);
        $fields = [];
        $fields[] = $model->title;

        $data = Helper::getUsableData($model);

        $element = self::findTypeInAddin($data->addin, 'Ключевые слова');
        if (isset($element->params)) {
            foreach ($element->params as $key => $value) {
                $fields[] = $value->value;
            }
        }

        $element = self::findTypeInAddin($data->addin, 'Комплектация');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }

        $element = self::findTypeInAddin($data->addin, 'Назначение');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }

        $element = self::findTypeInAddin($data->addin, 'Направление');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }

        $element = self::findTypeInAddin($data->addin, 'Описание');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }
        $counter = 0;
        foreach ($fields as $field) {
            foreach ($keywords as $key => $keyword) {
                if (strripos($field, $keyword) !== false) {
                    $counter++;
                    unset($keywords[$key]);
                }
            }
        }

        if ($keywordsCount > 0) {
            return ($counter / $keywordsCount) * 100;
        } else {
            return 0;
        }
    }

    /**
     * Поиск элемента в addin
     */
    private static function findTypeInAddin($addin, $name)
    {
        foreach ($addin as $in) {
            if ($in->type == $name) {
                return $in;
            }
        }
        return null;
    }

    /**
     * Подсчёт полноты заполненности вкладки поиска
     * @return float
     */
    public static function calculateKeywordsFull($model): float
    {
        $keywords = $model->keywords()->get()->pluck('name');
        $fields = [];
        $fields[] = $model->title;
        $fieldsCount = 8; // Пока константа - 8 полей на странице

        $data = Helper::getUsableData($model);

        $element = self::findTypeInAddin($data->addin, 'Ключевые слова');
        if (isset($element->params)) {
            foreach ($element->params as $key => $value) {
                $fields[] = $value->value;
            }
        }

        $element = self::findTypeInAddin($data->addin, 'Комплектация');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }

        $element = self::findTypeInAddin($data->addin, 'Назначение');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }

        $element = self::findTypeInAddin($data->addin, 'Направление');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }

        $element = self::findTypeInAddin($data->addin, 'Описание');
        if (isset($element->params) && isset($element->params[0]->value)) {
            $fields[] = $element->params[0]->value;
        }

        $filledFields = 0;
        foreach ($fields as $field) {
            if (!empty($field)) {
                $filledFields++;
            }
        }

        $percent = $fieldsCount ? intval(($filledFields / $fieldsCount) * 100) : 0;
        if ($percent > 100) {
            $percent = 100;
        }
        return $percent;
    }

    public static function calculateVisibilityOptimization($product)
    {
        $characteristics = self::getWbCharacteristics($product);
        $allPopularity = 0;
        $allCharacteristics = [];
        $allTypes = [];
        foreach ($characteristics as $characteristic) {
            if (isset($characteristic->isBoolean) and $characteristic->isBoolean) continue;
            if (isset($characteristic->isNumber) and $characteristic->isNumber) continue;
            $allTypes[] = $characteristic->type;
            $allCharacteristics[$characteristic->type] = isset($characteristic->dictionary) ? $characteristic->dictionary : null;
            if (!isset($characteristic->dictionary) || $characteristic->dictionary == '/ext') {
                $directoryId = WbDirectory::firstWhere('slug', 'LIKE', '%' . 'ext')->id;
                $data = WbSearchService::searchCharacteristics($characteristic->type, $product->category->name, '', $directoryId, true,"25");

                if (isset($data['items'])) {
                    $allPopularity += $data['items'];
                }
            }
        }
        $existPopularity = 0;
        $data = Helper::getUsableData($product)->addin;
        foreach ($data as $exist) {
            /* Проверка на наличие характеристики в списке "без исключений" и проверка её заполненности */
            if (!in_array($exist->type, $allTypes) || !isset($exist->params)) {
                continue;
            }
            foreach ($exist->params as $parameter) {
                /* Проверка на наличие значения в параметрах */
                if (!isset($parameter->value)) {
                    continue;
                }
                /* Проверка на наличие ссылки на справочник внутри характеристики по которой идет поиск */
                if (!isset($allCharacteristics[$exist->type])) {
                    break;
                }
                $dictionary = $allCharacteristics[$exist->type];
                if ($dictionary != '/ext') {
                    $directory = WbDirectory::firstWhere('slug', 'LIKE', '%' . $dictionary);
                    $total = WbDirectoryItem::where('wb_directory_id', $directory->id)
                        ->where('title', 'LIKE', '%' . $parameter->value . '%')
                        ->select(DB::raw("sum(popularity) as total"))->first()->total;
                    $existPopularity += $total;
                } else {
                    $directoryId = WbDirectory::firstWhere('slug', 'LIKE', '%' . 'ext')->id;
                    $total = WbSearchService::searchCharacteristics($exist->type, $product->category->name, $parameter->value, $directoryId, true,"25");
                    if (isset($total['items'])) {
                        $existPopularity += $total['items'];
                    }
                }
            }
        }
        if (!$allPopularity) {
            return 0;
        }
        $percent = intval(($existPopularity / $allPopularity) * 100);
        if ($percent > 100) {
            $percent = 100;
        }
        return $percent;
    }

    /*
     * Характеристики для товара WB без исключений
     */
    public static function getWbCharacteristics($product)
    {
        $characteristicsExcluded = [];

        if (!optional($product->recommended_characteristics)->addin) {
            return  $characteristicsExcluded;
        }

        $excludeCharacteristics = ['Код ролика на YouTube', 'Аннотация', 'Изображения', '3D-изображение', 'Название', 'Наименование', 'Ключевые слова'];

        foreach ($product->recommended_characteristics->addin as $item) {
            if (!in_array($item->type, $excludeCharacteristics)) {
                $item->isBoolean = (bool)YesNoWbCharacteristics::where('category', $product->object)->where('characteristic', $item->type)->count();
                $item->useOnlyDictionaryValues = $item->useOnlyDictionaryValues || !NotShowingWbCharacteristics::where('category', $product->object)->where('characteristic', $item->type)->count();
                $characteristicsExcluded[] = $item;
            }
        }
        return $characteristicsExcluded;
    }
}
