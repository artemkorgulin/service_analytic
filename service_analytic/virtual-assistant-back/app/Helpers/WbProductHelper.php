<?php

namespace App\Helpers;

use App\Classes\Helper;
use App\Models\WbCategory;

/**
 * Helper for Wildberries product (card and nomenclatures)
 */
class WbProductHelper
{
    /**
     * Clear data array from
     */
    public static function clearCardAddins(&$card)
    {
        $card = dataToObject($card);
        $WbProductTypeCharacteristics = self::getWbProductTypeFromCard($card);
        $data = Helper::getUsableData($WbProductTypeCharacteristics);
        if (!isset($data->addin) || !isset($card->addin)) {
            return $card;
        }
        foreach ($card->addin as $key => $addin) {
            if ($addin->params === [] || !isset($addin->type)) {
                unset($card->addin[$key]);
                continue;
            }
            $type = 'string';
            $data = Helper::getUsableData($WbProductTypeCharacteristics);
            if (self::getTypeForAddins($data->addin, $addin->type) === 'number') {
                $type = 'number';
            }

            foreach ($card->addin[$key]->params as $key2 => $param) {
                if (isset($param->value) && $type === 'string' && is_string($card->addin[$key]->params[$key2]->value)) {
                    $card->addin[$key]->params[$key2]->value = strval($card->addin[$key]->params[$key2]->value);
                } elseif (isset($param->value) && $type === 'number') {
                    $card->addin[$key]->params[$key2]->value = floatval($card->addin[$key]->params[$key2]->value);
                } elseif (isset($param->count) && isset($param->units)) {
                    $card->addin[$key]->params[$key2]->count = floatval($card->addin[$key]->params[$key2]->count);
                    $card->addin[$key]->params[$key2]->units = strval($card->addin[$key]->params[$key2]->units);
                }
            }

            $card->addin[$key]->params = array_values((array)$card->addin[$key]->params);
        }

        $card->addin = array_values($card->addin);
        $card = dataToObject($card);
        return $card;
    }

    /**
     * Return addins type
     * Search in addins (addins from products types/catalog categories) characteristic and return 'number'
     * or string type
     * @param array $addins
     * @param $type
     * @return false|string
     */
    private static function getTypeForAddins($addins, $type): bool|string
    {
        foreach ($addins as $addin) {
            if ($addin->type === $type && isset($addin->isNumber) && $addin->isNumber) {
                return 'number';
            } elseif ($addin->type === $type) {
                return 'string';
            }
        }
        return false;
    }

    /**
     * Return array with product type
     * @param $card
     * @return WbCategory|bool
     */
    public static function getWbProductTypeFromCard($card): bool|WbCategory
    {
        $card = dataToObject($card);
        if (isset($card->object) && $card->object && isset($card->parent) && $card->parent) {
            return WbCategory::where([
                'name' => $card->object,
                'parent' => $card->parent
            ])->first();
        } else {
            return false;
        }
    }

    /**
     * @param string $type
     * @param string $category
     * @return void
     */
    public static function getWbCharacteristicByTypeAndProductsCategory(string $type, string $category):?object {

        $wbCategory = WbCategory::query()->firstWhere('name', $category);

        if (!$wbCategory || isset($wbCategory->data->addin) || !$wbCategory->data->addin) {
             return null;
         }

         foreach ($wbCategory->data->addin as $addin) {
             if ($addin->type === $type) {
                 return $addin;
             }
         }

         return null;
    }

}
