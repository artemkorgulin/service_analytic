<?php

use App\Classes\Helper;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\BlackListBrandController as BlackListBrandController;
use App\Http\Controllers\Api\Common\CommonProductController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\Ozon\ProductsController as OzonProductsController;
use App\Http\Controllers\Api\v2\OzCategoriesController as CategoriesController;
use App\Http\Controllers\Api\v2\OzFeaturesController;
use App\Http\Controllers\Api\v2\ProductsController;
use App\Http\Controllers\Api\Wildberries\BarcodesController as WildberriesBarcodesController;
use App\Http\Controllers\Api\Wildberries\CategoriesController as WildberriesCategoriesController;
use App\Http\Controllers\Api\Wildberries\DirectoriesController as WildberriesDirectoriesController;
use App\Http\Controllers\Api\Wildberries\ProductsController as WildberriesProductsController;
use App\Http\Controllers\Api\Wildberries\AccessValidatorController as WildberriesAccessValidatorController;
use App\Http\Controllers\Api\Ozon\OzCompanyProductController;
use App\Http\Controllers\Api\Wildberries\WbCompanyProductController;
use App\Http\Controllers\EscrowController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Main\FirstStepController;
use App\Http\Controllers\Main\SecondStepController;
use App\Http\Controllers\Main\ThirdStepController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OzCategoriesController;
use App\Http\Controllers\OzProductAnalyticsDataController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\WbPickListController;
use App\Http\Controllers\WbUsingKeywordController;
use App\Http\Middleware\CheckUserAccount;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/**
 * Routes and controllers for inner services
 */
Route::group(['prefix' => 'inner'], function () {
    Route::group(['prefix' => 'reports'], function (Router $router) {
        $router->post('ozon/analytics-data', [OzProductAnalyticsDataController::class, 'getAnalyticsData']);
    });
});

/**
 * Api v2
 */
Route::group(['prefix' => 'v2'], function (Router $router) {
    $router->post('notifications/new-account', [NotificationsController::class, 'newAccount']);
    $router->resource('black-list-brands', BlackListBrandController::class);
    $router->post('add-product', [ProductsController::class, 'addProduct']);
    $router->post('verify-product', [ProductsController::class, 'setVerifiedStatus']);
    $router->post('add-products', [ProductsController::class, 'massAddProducts']);
    // Добавление или удаление товаров из отслеживания
    $router->put('update-products', [ProductsController::class, 'updateProducts']);
    $router->delete('remove-products', [ProductsController::class, 'removeProducts']);

    /**
     * Получить значение для справочника товара по
     */
    $router->resource('features', OzFeaturesController::class)->only(['show']);

    /**
     * Получение типа товаров
     */
    $router->any('oz/categories', [CategoriesController::class, 'getProductTypes']);
    // Todo тоже самое в последствии переделать на front'е
    $router->any('ozon-categories', [CategoriesController::class, 'getProductTypes']);

    $router->any('ozon/account-categories', [CategoriesController::class, 'getAccountProductCategory'])
        ->middleware(CheckUserAccount::class);
    /**
     * Получение полей типа товара ID не Ozon а наш!
     */
    $router->any('oz/categories/{id}', [CategoriesController::class, 'getProductTypeFields']);

    /**
     * Получение значения справочников по ID характеристики с типу товара
     */
    $router->get('oz/categories/{id}/directories/{directory_id}', [CategoriesController::class, 'getProductTypes']);

    /**
     * Получение значения справочников по ID характеристики (Id характеристики именно)
     */
    $router->any('oz/directories/{directory_id}', [CategoriesController::class, 'getDirectoryValues']);

    /**
     * Создаем штрих кодов EAN13
     */
    $router->any('generate-barcode', function () {
        return response()->json([
            'barcode' => Helper::genEAN13(),
        ]);
    });

    /**
     * Товары
     */
    $router->get('products', [ProductsController::class, 'getProductsList']);
    $router->get('products/pagination/{count}', [ProductsController::class, 'setPagination'])
        ->where('count', '[0-9]+');

    /**
     * Получение метода
     */
    // Первым методом генерируем список полей, необходимых для заполнения в для создания продукта
    $router->get('create-product/{categoryId}', [ProductsController::class, 'createProductView']);
    // Второй метод необходим
    $router->post('create-product/{categoryId}', [ProductsController::class, 'createProduct']);
    // Отправляем в Ozon
    $router->get('ozon/product-send/{id}', [ProductsController::class, 'sendProduct']);

    $router->get('products/sample', [ProductsController::class, 'getSampleProductDetail']);
    $router->get('products/{id}', [ProductsController::class, 'getProductDetail']);
    $router->get('products/{id}/download-pdf-recomendations', [ProductsController::class, 'downloadPdfRecomendation']);
    $router->get('products/{id}/search-characteristics', [ProductsController::class, 'searchFeatureValues']);

    // Поисковая оптимизация Ozon
    $router->post('add_goods_list', [OzonProductsController::class, 'addGoodsList']);
    $router->delete('oz_list_goods_adds', [OzonProductsController::class, 'destroyGoodsList']);
    $router->post('create_goods_list_user ', [OzonProductsController::class, 'createGoodsListUser']);
    $router->get('key_requests', [OzonProductsController::class, 'getKeyRequests']);
    $router->get('saved_goods_list', [OzonProductsController::class, 'getPickList']);

    // Редактирование продукта с сохранением информации в Ozon и в базу данных
    $router->put('products/{id}/modify-product', [ProductsController::class, 'modifyProduct']);
    $router->delete('products/{id}/clear-all-triggers', [ProductsController::class, 'clearAllTriggers']);
    $router->delete('products/{id}/clear-photo-triggers', [ProductsController::class, 'clearPhotoTriggers']);
    $router->delete('products/{id}/clear-review-triggers', [ProductsController::class, 'clearReviewTriggers']);
    $router->delete('products/{id}/clear-position-triggers', [ProductsController::class, 'clearPositionTriggers']);
    $router->delete('products/{id}/clear-feature-triggers', [ProductsController::class, 'clearFeatureTriggers']);
    $router->delete('products/{id}/clear-remove-from-sale-triggers',
        [ProductsController::class, 'clearRemoveFromSaleTriggers']);
    $router->delete('products/{id}/reset-update-flags', [ProductsController::class, 'resetUpdatedFlags']);
    $router->delete('products/{id}/reset-success-flag', [ProductsController::class, 'resetShowSuccessAlertFlag']);
    $router->get('products/test/count', [ProductsController::class, 'getTestProductsCount']);
    $router->get('export/product-characteristics/{id}', [ExportController::class, 'exportProductCharacteristics']);

    // Информация об использованных депонированиях (использовано / доступно)
    $router->get('escrow/limits/{id}/devide', [EscrowController::class, 'getEscrowDevideProduct']);
    $router->get('escrow/limits/{id}', [EscrowController::class, 'getLimitsProduct']);
    $router->get('escrow/limits', [EscrowController::class, 'getLimits']);
});

/**
 * Все методы для Wildberries
 */
Route::group(['prefix' => 'v2/wildberries'], function (Router $router) {
    $router->post('validate-access', [WildberriesAccessValidatorController::class, 'validateAccessData']);
    $router->post('validate-access/client', [WildberriesAccessValidatorController::class, 'validateAccessDataForClientId']);

    $router->get('get-abuse-pdf', [EscrowController::class, 'getWbAbusePdf']);
    $router->post('make-escrow', [EscrowController::class, 'escrowWildberries']);
    $router->get('show-escrow/{id}/{nmid}', [WildberriesProductsController::class, 'showEscrow']);
    $router->get('test', [WildberriesProductsController::class, 'test']);
    $router->any('barcodes', [WildberriesBarcodesController::class, 'create']);
    $router->any('categories/my', [WildberriesCategoriesController::class, 'myCategories']);
    $router->resource('categories', WildberriesCategoriesController::class)->only(['index', 'show']);

    $router->get('account-categories', [WildberriesCategoriesController::class, 'getAccountProductCategory'])
           ->middleware(CheckUserAccount::class);

    $router->resource('directories', WildberriesDirectoriesController::class)->only(['index', 'show']);
    // Пагинация продукта Wildberries
    $router->get('products/pagination/{count}', [WildberriesProductsController::class, 'setPagination'])
        ->where('count', '[0-9]+');
    // Удаление товаров (мягкое)
    $router->any('products/delete', [WildberriesProductsController::class, 'destroy']);

    $router->put('products/mass-update', [WildberriesProductsController::class, 'massUpdate']);

    // Синхронизация продукта Wildberries
    $router->get('products/{id}/sync', [WildberriesProductsController::class, 'sync'])
        ->where('id', '[0-9]+');

    $router->get('product-sync/{id}', [WildberriesProductsController::class, 'sync'])
        ->where('id', '[0-9]+');
    $router->get('select-not-active-brands', [WildberriesProductsController::class, 'selectNotActiveBrands']);
    $router->get('select-not-active-products', [WildberriesProductsController::class, 'selectNotActiveProducts']);

    $router->post('activate-not-active-products', [WildberriesProductsController::class, 'activateNotActiveProducts']);

    $router->resource('products', WildberriesProductsController::class)->only(['index', 'show', 'store', 'update']);

    $router->post('pick_list', [WbPickListController::class, 'store']);
    $router->get('pick_list', [WbPickListController::class, 'index']);
    $router->get('key_requests', [WbPickListController::class, 'getKeyRequests']);
    $router->delete('pick_list', [WbPickListController::class, 'destroy']);
    $router->get('saved_pick_list', [WbPickListController::class, 'getPickList']);

    $router->get('using_keywords', [WbUsingKeywordController::class, 'index']);

    $router->get('dashboard/filter-products', [WildberriesProductsController::class, 'getProductsBySegmentFilter']);

    $router->post('platform/get-products-price', [WildberriesProductsController::class, 'getPlatformProductsPrice']);
});

Route::group(['prefix' => 'v2/dashboard', 'middleware' => CheckUserAccount::class], function (Router $router) {
    $router->get('data', [DashboardController::class, 'getDataByDashboardType']);
});

/**
 * Все методы для Ozon (новый)
 */
Route::group(['prefix' => 'v2/ozon'], function (Router $router) {
    $router->get('get-abuse-pdf', [EscrowController::class, 'getOzonAbusePdf']);
    $router->post('make-escrow', [EscrowController::class, 'escrowOzon']);
    $router->resource('products', OzonProductsController::class)->only(['index', 'show']);
    $router->get('select-not-active-brands', [OzonProductsController::class, 'selectNotActiveBrands']);
    $router->get('select-not-active-products', [OzonProductsController::class, 'selectNotActiveProducts']);
    $router->post('activate-not-active-products', [OzonProductsController::class, 'activateNotActiveProducts']);
    $router->get('get-positions-history', [ProductsController::class, 'getPositionsHistory']);
    $router->get('get-analytics-data', [ProductsController::class, 'getAnalyticsData']);
    $router->get('detail/recommendations/{id}', [OzonProductsController::class, 'getDetailRecommendations'])
        ->where('id', '[0-9]+');;

    Route::group(['middleware' => CheckUserAccount::class], function (Router $router) {
        // Массовое редактирование продуктов с сохранением информации в Ozon и в базу данных
        $router->put('products/update-mass', [OzonProductsController::class, 'modifyProductMass'])
            ->name('ozon.products.update.mass');

        // Получения списка товаров с характеристиками
        $router->get(
            'product/list-with-feature',
            [OzonProductsController::class, 'getProductsListWithFeature']
        )
            ->name('ozon.products.list.with.feature');

        ///////// Dashboards /////////
        // Получение характеристик категории
        $router->get('category/feature-list', [OzonProductsController::class, 'categoryFeatureList']);
        $router->get('dashboard/filter-products', [OzonProductsController::class, 'getProductsBySegmentFilter']);

        $router->post('platform/get-products-price', [OzonProductsController::class, 'getPlatformProductsPrice']);
    });
});


/**
 * Api for corporate (company) model
 */
Route::group(['prefix' => 'v2/company-model/ozon'], function (Router $router) {
    $router->post('create-products-by-ids', [OzCompanyProductController::class, 'createProductsByIds']);
    $router->post('create-products-by-external-ids', [OzCompanyProductController::class, 'createProductsByExternalIds']);
    $router->post('move-products', [OzCompanyProductController::class, 'moveProducts']);
    $router->post('delete-products', [OzCompanyProductController::class, 'deleteProducts']);
    $router->post('delete-products-for-all-account-users', [OzCompanyProductController::class, 'deleteProductsForAllAccountUsers']);
});

Route::group(['prefix' => 'v2/company-model/wildberries'], function (Router $router) {
    $router->post('create-products-by-ids', [WbCompanyProductController::class, 'createProductsByIds']);
    $router->post('create-products-by-imt-ids', [WbCompanyProductController::class, 'createProductsByImtIds']);
    $router->post('move-products', [WbCompanyProductController::class, 'moveProducts']);
    $router->post('delete-products', [WbCompanyProductController::class, 'deleteProducts']);
    $router->post('delete-products-for-all-account-users', [WbCompanyProductController::class, 'deleteProductsForAllAccountUsers']);
});

/**
 * Api v1
 */
Route::group(['prefix' => 'v1'], function (Router $router) {
    $router->resource('images', ImageController::class)->except([
        'create', 'edit', 'update'
    ]);

    $router->resource('files', FileController::class)->except([
        'create', 'edit', 'update'
    ]);

    $router->get('ozon-categories', [CategoriesController::class, 'index']);
    $router->get('get-ozon-category-params', [CategoriesController::class, 'params']);
    $router->get('get-ozon-category-params/{id}', [CategoriesController::class, 'param']);
    $router->get('step1', [FirstStepController::class, 'index']);
    $router->get('get-root-queries', [FirstStepController::class, 'getRootQueries']);
    $router->get('get-ozon-categories', [FirstStepController::class, 'getOzonCategories']);
    $router->get('step2', [SecondStepController::class, 'index']);
    $router->get('step3', [ThirdStepController::class, 'index']);
    $router->get('make-new-perfect-title', [ThirdStepController::class, 'formNewPerfectTitle']);

    $router->get('export/root-queries', [ExportController::class, 'exportRootQueries']);
    $router->get('export/search-queries', [ExportController::class, 'exportSearchQueries']);
    $router->get('export/negative-keywords', [ExportController::class, 'exportNegativeKeywords']);

    $router->post('get-keywords-popularity', [ApiController::class, 'getKeywordsPopularity']);
    $router->post('find-keywords-with-statistics', [ApiController::class, 'findKeywordsWithStatistics']);

    $router->get('product_statistic', [StatisticController::class, 'getProductStatistic']);
    $router->get('top_position_product', [StatisticController::class, 'getTopPositionProduct']);

    $router->get('user_categories', [OzCategoriesController::class, 'getCategoriesForSearchOptimization']);
});
