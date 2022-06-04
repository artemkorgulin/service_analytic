<?php

namespace App\Helpers;

use App\Classes\Helper;
use App\Models\WbCategory;

/**
 * Helper for Wildberries characteristic
 */
class WbCharacteristicsHelper
{
    /**
     * @param array $productAddin
     * @return int
     */
    public static function removeCharacteristicTitle($productAddin)
    {
        foreach ($productAddin->addin as $incrementer => $characteristic) {
            if ($characteristic->type === 'title') {
                unset($productAddin->addin[$incrementer]);
            }
        }

        return $productAddin;
    }
}
