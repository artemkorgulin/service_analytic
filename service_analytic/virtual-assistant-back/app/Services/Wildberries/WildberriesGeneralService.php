<?php

namespace App\Services\Wildberries;

use App\Helpers\Controller\CommonControllerHelper;
use App\Http\Controllers\Api\Wildberries\CommonController;
use App\Models\WbProduct;
use App\Models\WbTemporaryProduct;
use App\Repositories\Interfaces\Wildberries\WildberriesProductRepositoryInterface;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * This class contains methods for all controllers
 */
class WildberriesGeneralService extends CommonController implements WildberriesProductRepositoryInterface
{
    /**
     * Get params from request only with Request use it in controllers
     * @param Request $request
     * @return CommonControllerHelper
     */
    protected function getParamsFromRequest(Request $request): CommonControllerHelper
    {
        $params = $this->getParams($request);
        if (app()->runningInConsole() === false && $params->user) {
            if ($request->perPage) {
                $params->pagination = (int)$request->perPage;
                Cache::put($params->user['id'] . $this->paginationPostfix, $params->pagination);
            } elseif (Cache::has($request->user['id'] . $this->paginationPostfix)) {
                $params->pagination = Cache::get($params->user['id'] . $this->paginationPostfix);
            }
        }

        return $params;
    }

    /**
     * Check and deactivate products more than in tariff available
     */
    public function checkAndDeactivateProductQty()
    {
        if (isset(request()->tariffs)) {
            $sku = UserService::getMaxProductsCount();
            $activeProductCount = WbProduct::currentAccount()->currentUser()->count();
            if ($activeProductCount > $sku) {
                $productsForDeactivate = $activeProductCount - $sku;
                $imtIdField = 'wb_products.imt_id';
                $imtIds = WbProduct::select($imtIdField)->currentUser()->currentAccount()
                    ->latest()
                    ->limit($productsForDeactivate)
                    ->pluck($imtIdField)->toArray();
                if (!empty($imtIds)) {
                    $productToDelete = WbProduct::whereIn($imtIdField, $imtIds);
                    $productToDelete->delete();
                    // Restore products in temporary table
                    WbTemporaryProduct::withTrashed()->currentAccount()
                        ->whereIn('imt_id', $imtIds)
                        ->restore();
                }
            }
        }
    }
}
