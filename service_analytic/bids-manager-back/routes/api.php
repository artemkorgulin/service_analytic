<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\Api\V2\Campaign\CampaignController;
use App\Http\Controllers\Frontend\Campaign\CampaignProductController;
use App\Http\Controllers\Frontend\Product\ProductController;
use App\Http\Controllers\Frontend\Autoselect\CategoryController;
use App\Http\Controllers\Frontend\Autoselect\AutoselectController;
use App\Http\Controllers\Frontend\Words\WordsController;
use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\AnalyticController;
use App\Http\Controllers\Frontend\ReportController;
use App\Http\Controllers\Api\V2\Campaign\GroupController;
use App\Http\Controllers\Api\V2\Campaign\KeywordController as CampaignKeywordController;
use App\Http\Controllers\Api\V2\Campaign\StopWordController as CampaignStopWordController;
use App\Http\Controllers\Api\V2\KeywordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'v1'], function () {
    // УС 1.0
    //Route::get('get-all-filters-list', [CatalogController::class, 'getAllFiltersList']);
    //Route::get('get-all-campaigns-list', [CatalogController::class, 'getAllCampaignsList']);
    Route::get('get-all-products-list', [CatalogController::class, 'getAllProductsList']);
    //Route::get('get-all-campaigns-statuses-list', [CatalogController::class, 'getAllCampaignsStatusesList']);
    //Route::get('get-all-statuses-list', [CatalogController::class, 'getAllStatusesList']);
    Route::get('get-counters-decode', [CatalogController::class, 'getCountersDecode']);

    Route::get('get-analytics-list', [AnalyticController::class, 'getAnalyticsList']);
    //Route::get('get-campaigns-analytics-list', [AnalyticController::class, 'getCampaignsList']);
    Route::get('get-counters-list', [AnalyticController::class, 'getCounters']);
    Route::get('/get-products-list', [AnalyticController::class, 'getProductsList']);
    //Route::get('/get-keywords-list', [AnalyticController::class, 'getKeywordsList']);

    Route::get('/get-analytics-report', [ReportController::class, 'getAnalyticsReport']);
    Route::get('/get-campaigns-statistic-report', [ReportController::class, 'getCampaignsStatisticReport']);
    Route::get('/get-products-statistic-report', [ReportController::class, 'getProductsStatisticReport']);
    Route::get('/get-keywords-statistic-report', [ReportController::class, 'getKeywordsStatisticReport']);

    // УС 2.0
    /*
    Route::get('/get-all-strategies-types', [CatalogController::class, 'getAllStrategiesTypes']);
    Route::get('/get-all-strategies-statuses', [CatalogController::class, 'getAllStrategiesStatuses']);

    Route::get('/get-strategies-list', 'Frontend\StrategyController@getStrategiesList');
    Route::get('/get-strategy-campaigns-list', 'Frontend\StrategyController@getStrategyCampaignsList');
    Route::get('/get-strategy-campaign-history', 'Frontend\StrategyController@getStrategyCampaignHistory');
    Route::get('/get-count-company-for-strategy-cpo', [StrategyController::class, 'getCountCompanyForStrategyCpo']);

    Route::get('/get-campaigns-list-for-strategy', 'Frontend\StrategyController@getCampaignsListForStrategy');
    Route::get('/get-keywords-list-for-strategy', 'Frontend\StrategyController@getKeywordsListForStrategy');
    Route::get('/get-default-horizon-strategy-cpo', 'Frontend\StrategyController@getDefaultHorizonStrategyCpo');

    Route::get('/get-strategy-campaign-history-xls', 'Frontend\ReportController@getStrategyCampaignHistoryXls');
    */
    /*** Изменение данных ***/

    // update data in database and in ozon
    /*Route::get('/update-keyword-bet', 'Api\UpdateController@updateKeywordBid');
    Route::get('/update-keyword-status', 'Api\UpdateController@updateKeywordStatus');
    Route::get('/update-campaign-budget', 'Api\UpdateController@updateCampaignBudget');
    Route::get('/update-campaign-status', 'Api\UpdateController@updateCampaignStatus');
    Route::get('/update-campaign-product-status', 'Api\UpdateController@updateCampaignProductStatus');

    // УС 2.0
    Route::post('/add-campaign-to-strategy', 'Api\UpdateController@addNewStrategy');
    Route::post('/update-strategy-status', 'Api\UpdateController@updateStrategyStatus');
    Route::post('/update-strategy-threshold', 'Api\UpdateController@updateStrategyThreshold');
    Route::post('/update-strategy-step', 'Api\UpdateController@updateStrategyStep');
    Route::post('/update-strategy-tcpo', 'Api\UpdateController@updateStrategyTcpo');
    Route::post('/update-strategy-cpo', 'Api\UpdateController@updateStrategyCpo');
    Route::delete('/delete-strategy', 'Api\UpdateController@deleteStrategy');
*/
    // РК 2.3
    Route::get('/campaign/products', [CampaignProductController::class, 'index']);
    Route::get('/products', [ProductController::class, 'getFilteredList']);
    Route::get('/products/multisearch', [ProductController::class, 'searchProductsBySku']);
    Route::post('/campaign/products/store', [CampaignProductController::class, 'storeMultiple']);
    Route::post('/campaign/products/ids/store/', [CampaignProductController::class, 'storeMultipleIds']);
    Route::post('/campaign/products/{campaignId}/update', [CampaignProductController::class, 'updateMultiple']);
    Route::post('/bids/campaign/product/status/update', [CampaignProductController::class, 'updateStatus']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/autoselect/run', [AutoselectController::class, 'run']);
    Route::post('/autoselect/results', [AutoselectController::class, 'getResultList']);
    Route::post('/autoselect/results/sheet', [AutoselectController::class, 'getXLSResults']);
    Route::get('/autoselect/keywords/template', [AutoselectController::class, 'getXLSKeywordUploadTemplate']);
    Route::post('/autoselect/words/save', [WordsController::class, 'saveFromAutoselect']);

    Route::post('/words/upload', [WordsController::class, 'saveFromXls']);

    Route::post('check-products-campaigns', [ApiController::class, 'checkProductsCampaigns']);
});

Route::group(['prefix' => 'v2'], function () {
    Route::apiResource('/campaigns', CampaignController::class);
    Route::get('/campaigns-search', [CampaignController::class, 'search']);
    Route::get('/campaign-filters', [CampaignController::class, 'getAllFilters']);
    Route::get('/campaigns-statistic', [CampaignController::class, 'campaignsStatistic']);
    Route::get('/keywords', [KeywordController::class, 'index']);
    Route::apiResource('/campaign/{campaign}/groups', GroupController::class);
    Route::apiResource(
        '/campaign/{campaign}/keywords',
        CampaignKeywordController::class,
        ['except' => ['update', 'destroy']]
    );
    Route::put('/campaign/{campaign}/keywords', [CampaignKeywordController::class, 'updateBids']);
    Route::delete('/campaign/{campaign}/keywords', [CampaignKeywordController::class, 'destroy']);
    Route::apiResource(
        '/campaign/{campaign}/stop-words',
        CampaignStopWordController::class,
        ['except' => ['update', 'destroy']]
    );
    Route::delete('/campaign/{campaign}/stop-words', [CampaignStopWordController::class, 'destroy']);
});
