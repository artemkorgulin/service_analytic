<?php

namespace App\Services\Ozon;

use App\Http\Requests\Ozon\OzonProductMassUpdateRequest;
use App\Jobs\DashboardAccountUpdateJob;
use App\Jobs\OzonCheckProductMassChangesJob;
use App\Models\OzProduct;
use App\Services\ProductCommon\CommonProductUpdateService;
use App\Services\UserService;
use App\Services\V2\ProductServiceUpdater;

class OzonProductUpdateMassService
{
    /**
     * @param array $updateArrayProducts
     * @param array $user // From web app service
     * @param array $account // From web app service
     * @return void
     * @throws \Exception
     */
    public function massUpdateRunner(
        OzonProductMassUpdateRequest $updateArrayProducts
    ): void {
        $requestArray = $updateArrayProducts->all();

        $commonService = \App::make(CommonProductUpdateService::class);
        $commonService->initModel(new OzProduct());
        $commonService->updateProductsBlocked(array_column($requestArray['items'], 'id'), true);

        $this->productUpdateMassFromArray($requestArray['items']);

        $commonService->updateProductsBlocked(array_column($requestArray['items'], 'id'), false);

        if (app()->runningInConsole() === false) {
            OzonCheckProductMassChangesJob::dispatch(
                array_column($requestArray['items'], 'id'),
                UserService::getCurrentAccount(),
                UserService::getUser()
            )
                ->onQueue('default_long')
                ->delay(now()->addMinutes(5));

            DashboardAccountUpdateJob::dispatch(
                UserService::getUserId(),
                UserService::getAccountId(),
                UserService::getCurrentAccountPlatformId()
            )->delay(now()->addMinutes(7));

        }
    }

    /**
     * @param array $updateArrayProducts
     * @return void
     * @throws \Exception
     */
    public function productUpdateMassFromArray(array $updateArrayProducts)
    {
        // @TODO refactor global request object and use Job
        foreach ($updateArrayProducts as $product) {
            $productService = new ProductServiceUpdater($product['id']);
            $productService->updateFromArray($product);
        }
    }
}
