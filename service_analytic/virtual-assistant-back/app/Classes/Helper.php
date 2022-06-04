<?php


namespace App\Classes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use stdClass;

class Helper
{

    private static $barcodePrefix = '20';

    private static $possibleOzonBrandAttributeIds = [85, 31];

    private static $noBrandOzon = 'Нет бренда';

    /**
     * Get param name from card object
     *
     * @param $card
     * @return mixed
     */
    public static function wbCardGetTitle($card): mixed
    {
        $card = dataToObject($card);
        if (empty($card->addin)) {
            return null;
        }
        foreach ($card->addin as $addin) {
            if ($addin->type === 'Наименование') {
                return $addin->params[0]->value;
            }
        }
        return null;
    }

    /**
     * Рекурсивно массив в объект
     * @param $array
     * @return stdClass
     */
    public static function arrayToObject($array)
    {
        // First we convert the array to a json string
        $json = json_encode($array);
        // The we convert the json string to a stdClass()
        return json_decode($json);
    }

    /**
     * Get param name from card object
     * @param $card
     * @return mixed
     */
    public static function wbCardGetBrand($card)
    {
        $card = self::arrayToObject($card);
        foreach ($card->addin as $addin) {
            if ($addin->type === 'Бренд') {
                // See task SE-996
                return trim($addin->params[0]->value);
            }
        }
        return null;
    }

    /**
     * Get param name from card object
     * @param $card
     * @return array
     */
    public static function wbCardGetBarcodes($card): array
    {
        $barcodes = [];
        if (!is_object($card)) {
            $card = self::arrayToObject($card);
        }
        if (!empty($card->nomenclatures)) {
            foreach ($card->nomenclatures as $nm) {
                if (isset($nm->variations)) {
                    foreach ($nm->variations as $va) {
                        $barcodes = array_merge($barcodes, $va->barcodes);
                    }
                }
            }
        }
        return $barcodes;
    }

    /**
     * Get param name from card object
     * @param $card
     * @return mixed
     */
    public static function wbCardGetNmIds($card)
    {
        $card = self::arrayToObject($card);
        $nomenclatures = [];
        if (isset($card->nomenclatures)) {
            foreach ($card->nomenclatures as $nomenclature) {
                if ($nomenclature->nmId) {
                    $nomenclatures[] = $nomenclature->nmId;
                }
            }
        }
        return $nomenclatures;
    }

    /**
     * Wildberries card get value params for all nomenclatures (many)
     * @param $card
     * @param $type
     * @return null
     */
    public static function wbCardGetAllValueParams($card, $type)
    {
        return self::_wbCardGetValueParams($card, $type);
    }

    /**
     * Inner function for select parameters from variant
     * @param $card
     * @param $type
     */
    private static function _wbCardGetValueParams($card, $type, $onlyFirst = false): bool|array
    {
        $card = self::arrayToObject($card);
        $returned = [];
        if (!isset($card->nomenclatures)) {
            return false;
        }
        foreach ($card->nomenclatures as $nomenclature) {
            if (!isset($nomenclature->variations)) {
                return false;
            }
            foreach ($nomenclature->variations as $variant) {
                if (!isset($variant->addin)) {
                    return false;
                }
                foreach ($variant->addin as $addin) {
                    if ($addin->type === $type) {
                        foreach ($addin->params as $param) {
                            $returned[] = $param->value;
                        }
                        if ($onlyFirst) {
                            break;
                        }
                    }
                }
            }
        }
        return $returned;
    }

    /**
     * Get product price - first variant of nomenclatures
     * @param $card
     * @return mixed
     */
    public static function wbCardGetPrice($card)
    {
        return self::wbCardGetCountParam($card, 'Розничная цена');
    }

    /**
     * Wildberries card get count param (one)
     * @param $card
     * @param $type
     * @return null
     */
    public static function wbCardGetCountParam($card, $type)
    {
        return self::_wbCardGetParam($card, $type, 'count');
    }

    /**
     * Wildberries card get count param (one)
     * @param $card
     * @param $type
     * @return null
     */
    private static function _wbCardGetParam($card, $type, $whatSelect, $onlyFirst = true)
    {
        $card = self::arrayToObject($card);
        if (!isset($card->nomenclatures)) {
            return false;
        }
        foreach ($card->nomenclatures as $nomenclature) {
            if (!isset($nomenclature->variations)) {
                return false;
            }
            foreach ($nomenclature->variations as $variant) {
                if (!isset($variant->addin)) {
                    return false;
                }
                foreach ($variant->addin as $addin) {
                    if ($addin->type !== $type) {
                        continue;
                    }
                    foreach ($addin->params as $param) {
                        if ($onlyFirst) {
                            return $param->{$whatSelect};
                        }
                        $returned[] = $param->{$whatSelect};
                    }
                }
            }
        }

        return empty($returned) ? false : $returned;
    }

    /**
     * Get price range
     * @param collection $nomenclatures card nomenclatures such as WbProduct->nomenclatures()
     * @return false|null
     */
    public static function wbCardGetPriceRange(Collection $nomenclatures): bool|string|null
    {
        $min = $nomenclatures->min('price');
        $max = $nomenclatures->max('price');
        if ($min !== $max) {
            return "$min - $max";
        }
        return "$min";
    }

    /**
     * Get price range as array (See task SE-983)
     * @param array $card card data
     */
    public static function wbCardGetPriceRangeArray($card): array
    {
        // Set product price range
        $pricesCollection = collect(self::wbCardGetPrices($card));
        return [
            'price_min' => $pricesCollection->min(),
            'price_max' => $pricesCollection->max()
        ];
    }

    /**
     * Get product prices all
     * @param $card
     * @return mixed
     */
    public static function wbCardGetPrices($card)
    {
        return self::wbCardGetCountAllParam($card, 'Розничная цена');
    }

    /**
     * Wildberries card get count param (many)
     * @param $card
     * @param $type
     * @return null
     */
    public static function wbCardGetCountAllParam($card, $type)
    {
        return self::_wbCardGetParam($card, $type, 'count', false);
    }

    /**
     * Get url of Wildberries
     * @param $card
     * @return mixed
     */
    public static function wbCardGetUrl($card)
    {
        $card = self::arrayToObject($card);
        if (isset($card->nomenclatures[0]->nmId))
            return 'https://www.wildberries.ru/catalog/' . $card->nomenclatures[0]->nmId . '/detail.aspx';
        return null;
    }

    /**
     * Get first nomenclature photo
     * @param $card
     * @return mixed
     */
    public static function wbCardGetPhoto($card)
    {
        return self::wbCardGetValueParam($card, 'Фото');
    }

    /**
     * Wildberries card get value param (one)
     * @param $card
     * @param $type
     * @return null
     */
    public static function wbCardGetValueParam($card, $type)
    {
        $card = self::arrayToObject($card);
        if (isset($card->nomenclatures)) {
            foreach ($card->nomenclatures as $nomenclature) {
                if (!isset($nomenclature->addin)) {
                    return false;
                }
                foreach ($nomenclature->addin as $addin) {
                    if ($addin->type == $type) {
                        if (!isset($addin->params[0]->value)) {
                            return false;
                        }

                        return $addin->params[0]->value;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get photos for first nomenclature
     * @param $card
     * @return mixed
     */
    public static function wbCardGetImages($card)
    {
        return self::wbCardGetValueParams($card, 'Фото');
    }

    /**
     * Wildberries card get value params for first nomenclature (many)
     * @param $card
     * @param $type
     * @return null
     */
    public static function wbCardGetValueParams($card, $type)
    {
        return self::_wbCardGetValueParams($card, $type, true);
    }

    /**
     * Get all 360 photos for first nomenclature
     * @param $card
     * @return mixed
     */
    public static function wbCardGetPhotos360($card)
    {
        return self::wbCardGetValueParams($card, 'Фото360');
    }

    /**
     * Set photos360 for first nomenclature
     * @param $card
     * @param array $photos360
     * @return mixed
     * @throws \Exception
     */
    public static function wbCardSetPhotos360(&$card, array $photos360)
    {
        self::setWbAddinParams($card, $photos360, "Фото360");
        return $card;
    }

    /**
     * Set Wildberries addin params
     * $card
     * @throws \Exception
     */
    public static function setWbAddinParams(&$card, array $params, $type)
    {
        $card = self::arrayToObject($card);
        if (isset($card->nomenclatures)) {
            foreach ($card->nomenclatures as $a => $nomenclature) {
                if ($nomenclature->addin) {
                    $nomenclature->addin = (array)$nomenclature->addin;
                    $hasParam = false;
                    foreach ($nomenclature->addin as $b => $addin) {
                        if (isset($addin->type) && $addin->type === $type) {
                            $hasParam = true;
                            $card->nomenclatures[$a]->addin[$b]->params = [];
                            foreach ($params as $c => $param) {
                                $card->nomenclatures[$a]->addin[$b]->params[$c] =
                                    [
                                        "value" => $param
                                    ];
                            }
                        }
                    }
                    if (!isset($b)) {
                        $b = 0;
                    }
                    if ($hasParam === false) {
                        if (!isset($card->nomenclatures[$a]->addin[$b + 1]) || !$card->nomenclatures[$a]->addin[$b + 1]) {
                            $card->nomenclatures[$a]->addin[$b + 1] = new \StdClass();
                        }
                        $card->nomenclatures[$a]->addin[$b + 1]->type = $type;
                        foreach ($params as $c => $param) {
                            $card->nomenclatures[$a]->addin[$b + 1]->params[$c] =
                                [
                                    "value" => $param
                                ];
                        }
                    }
                }
            }
        }
        return $card;
    }

    /**
     * Set photos for first nomenclature
     * @param $card
     * @param array $photos360
     * @return mixed
     * @throws \Exception
     */
    public static function wbCardSetImages(&$card, array $images)
    {
        self::setWbAddinParams($card, $images, "Фото");
        return $card;
    }

    /**
     * Get first video for Wildberries product
     * @param $card
     * @return mixed
     */
    public static function wbCardGetVideo($card)
    {
        return self::wbCardGetValueParam($card, "Видео");
    }

    /**
     * Return "Ширина упаковки" addin param for Wildberries
     * @param $card
     * @return false|null
     */
    public static function wbCardGetPackingWidth($card)
    {
        return self::wbCardGetCountParam($card, "Ширина упаковки");
    }

    /**
     * Return "Глубина упаковки" addin param for Wildberries
     * @param $card
     * @return false|null
     */
    public static function wbCardGetPackingDepth($card)
    {
        return self::wbCardGetCountParam($card, "Глубина упаковки");
    }

    /**
     * Return "Высота упаковки" addin param for Wildberries
     * @param $card
     * @return false|null
     */
    public static function wbCardGetPackingHeight($card)
    {
        return self::wbCardGetCountParam($card, "Высота упаковки");
    }

    /**
     * Return "Высота упаковки" addin param units for Wildberries
     * @param $card
     * @return false|null
     */
    public static function wbCardGetPackingDimentionUnits($card)
    {
        return self::wbCardGetUnitsParam($card, "Ширина упаковки");
    }

    /**
     * Wildberries card get unit param (one)
     * @param $card
     * @param $type
     * @return null
     */
    public static function wbCardGetUnitsParam($card, $type)
    {
        return self::_wbCardGetParam($card, $type, 'units');
    }

    /**
     * Return "Вес товара с упаковкой (г)" addin param for Wildberries
     * @param $card
     * @return false|null
     */
    public static function wbCardGetPackingWeight($card)
    {
        return self::wbCardGetCountParam($card, "Вес товара с упаковкой (г)");
    }

    /**
     * Return "Высота упаковки" addin param for Wildberries
     * @param $card
     * @return false|null
     */
    public static function wbCardGetPackingWeightUnits($card)
    {
        return self::wbCardGetUnitsParam($card, "Вес товара с упаковкой (г)");
    }


    /**
     * Correct units Parameter.card.addin.params.units
     * @param $card
     * @return mixed
     */
    public static function wbCardCorrectUnits(&$card)
    {
        foreach ($card->addin as $key1 => $addin) {
            foreach ($addin->params as $key2 => $param) {
                $findAndUnset = false;
                if ((isset($param->units) && is_int($param->units)) || (isset($param->count) && !$param->count)) {
                    try {
                        unset($card->addin[$key1]->params[$key2]);
                        $findAndUnset = true;
                    } catch (Exception $exception) {
                        Log::error($exception->getMessage());
                    }

                }
                if ($findAndUnset && $key2 === 0) {
                    unset($card->addin[$key1]);
                }
            }
        }
        $card->addin = array_values($card->addin);
        return $card;
    }

    /**
     * Barcode generation
     */
    public static function genEAN13()
    {
        $string = self::$barcodePrefix . time();
        return $string . self::controlSymbolForEAN13($string);
    }

    /**
     * Контрольный символ
     * @param $string
     * @return string
     */
    private static function controlSymbolForEAN13($string)
    {
        $odd = 0;
        $even = 0;
        foreach (str_split($string, 1) as $i => $a) {
            if ($i % 2) {
                $even += $a;
            } else {
                $odd += $a;
            }
        }
        return (string)((10 - (($odd + $even * 3) % 10)) % 10);
    }

    /**
     * Получение бренда из карточки продукта по Ozon
     * @param $card
     * @return string
     */
    public static function ozCardGetBrand($card): string
    {
        if (!isset($card['attributes'])) {
            return self::$noBrandOzon;
        }

        foreach ($card['attributes'] as $attr) {
            if (isset($attr['attribute_id']) && (in_array($attr['attribute_id'], self::$possibleOzonBrandAttributeIds)) && isset($attr['values'][0]['value'])) {
                $brand = trim($attr['values'][0]['value']);
                if(strlen($brand) === 0){
                    return self::$noBrandOzon;
                }
                return $brand;
            }
        }

        return self::$noBrandOzon;
    }

    /**
     * Get product brand for Ozon from characteristics
     * @param $characteristics
     * @return string
     */
    public static function ozCardGetBrandFromCharacteristics($characteristics): string
    {
        foreach (self::$possibleOzonBrandAttributeIds as $possibleOzonBrandAttributeId) {
            // See task SE-996
            if (isset($characteristics[$possibleOzonBrandAttributeId]['selected_options'][0]['value'])) {
                return trim($characteristics[$possibleOzonBrandAttributeId]['selected_options'][0]['value']);
            }
        }
        return self::$noBrandOzon;
    }

    public static function getUsableData($data)
    {
        $data = dataToObject($data);
        $data = is_string($data->data) ? json_decode($data->data) : $data->data;
        $data = isset($data->scalar) ? json_decode($data->scalar) : $data;
        return $data;
    }
}
