<?php

namespace App\Services\Wildberries;

use App\Classes\Helper;
use App\Models\WbProductUser;
use App\Repositories\Interfaces\Wildberries\WildberriesProductRepositoryInterface;
use App\Repositories\Wildberries\WildberriesProductRepository;
use App\Services\Analytics\WbAnalyticsService;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;

use Illuminate\Http\Request;

class WilberriesListProductsService implements WildberriesProductRepositoryInterface
{
    public function getProductList
    (
        Request                       $request,
        WildberriesGeneralService     $generalService,
        WildberriesProductRepository  $productRepository,
        WbAnalyticsService            $analyticsService,
        WildberriesShowProductService $showProductService
    )
    {
        $generalService->checkAndDeactivateProductQty();
        $products = $productRepository->getSortedProducts(
            $request->input('categoryId'),
            $request->get('search'),
            $request->get('sortBy'),
            $request->get('sortType'),
            $request->get('categoryNames'),
            $request->get('withFeatures'),
            $request->get('availability')
        );
        $appends = $this->addPaginationAppends(
            $request->get('search'),
            $request->get('sortBy'),
            $request->get('sortType'),
            $request->get('categoryNames')
        );
        $products = PaginatorHelper::addPagination($request, $products)->setPath('')->appends($appends);

        // If open from mass update page
        if ($request->get('withCharacteristics')) {
            $products->map(function ($product) use ($showProductService) {
                return $showProductService->getProductFeatures($product);
            });
        }

        $products->map(function ($product) {
            $product->data = Helper::getUsableData($product);
            $quantity = 0;
            foreach ($product->nomenclatures()->get() as $nomenclature) {
                $quantity = $nomenclature->quantity;
            }
            $product->quantity = $quantity;
            return $product;
        });

        $deletedProducts = collect(['deletedProducts' => $productRepository->getCountOfDeletedProducts()]);
        $products = $deletedProducts->merge($products);

        return $products;
    }

    private function addPaginationAppends($search, $sortBy, $sortType, $categoryNames = []): array
    {
        $appends = [];

        if ($search) {
            $appends['search'] = $search;
        }

        if ($sortBy == 'price' || $sortBy == 'price_with_discount') {
            $appends['sortBy'] = 'price_with_discount';
        } else {
            $appends['sortBy'] = $sortBy;
        }

        if ($sortType) {
            $appends['sortType'] = $sortType;
        }

        if ($categoryNames) {
            $appends['categoryNames'] = $categoryNames;
        }

        return $appends;
    }

    /**
     * Get observed account imt_id products
     */
    public static function getAccountObservedProductImtIds(): ?array
    {
        return WbProductUser::currentAccount()->select(['imt_id'])->pluck('imt_id')->toArray();
    }
}
