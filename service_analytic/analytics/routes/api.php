<?php

use App\Http\Controllers\Api\V1\Action\HistoryProductController;
use App\Http\Controllers\Api\V1\Analysis\AnalysisBrandController;
use App\Http\Controllers\Api\V1\Analysis\AnalysisCategoryController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\Category\CategorySubjectsController;
use App\Http\Controllers\Api\V1\Category\CategoryStatisticController;
use App\Http\Controllers\Api\V1\Category\CategoryTreeController;
use App\Http\Controllers\Api\V1\Category\CategoryController;
use App\Http\Controllers\Api\V1\BrandStatisticController;
use App\Http\Controllers\Api\V1\CategoryAnalysisController;
use App\Http\Controllers\Api\V1\CommonUserStatisticController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\ProductDetailController;
use App\Http\Controllers\Api\V1\RatingController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\UserParamController;
use App\Http\Controllers\Api\V2\BrandStatisticController as ClickhouseBrandStatisticController;
use App\Http\Controllers\Api\V2\Category\CategoryController as ClickhouseCategoryController;
use Illuminate\Support\Facades\Route;

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
    Route::get('user-params', [UserParamController::class, 'index']);

    Route::get('common-user-statistic', [CommonUserStatisticController::class, 'index']);

    Route::get('top-position-product', [PositionController::class, 'getTopPositionProducts']);

    Route::group(['prefix' => 'wb'], function () {
        Route::get('subjects', [SubjectController::class, 'index']);

        Route::group(['prefix' => 'get'], function () {
            Route::get('categories', [CategoryTreeController::class, 'index']);
            Route::get('category-subjects', [CategorySubjectsController::class, 'getSubjects']);
            Route::get('ratings', [RatingController::class, 'getRatingsList']);
        });

        Route::group(['prefix' => 'statistic'], function () {
            Route::group(['prefix' => 'categories'], function () {
                Route::get('products', [CategoryController::class, 'getProducts']);
                Route::get('subcategories', [CategoryStatisticController::class, 'getSubcategories']);
            });

            Route::group(['prefix' => 'brands'], function () {
                Route::get('products', [BrandStatisticController::class, 'getProducts']);
                Route::get('categories', [BrandStatisticController::class, 'getCategories']);
                Route::get('sellers', [BrandStatisticController::class, 'getSellers']);
                Route::get('find', [BrandController::class, 'find']);
                Route::get('get', [BrandController::class, 'get']);
            });
            Route::group(['prefix' => 'category'], function () {
                Route::get('analysis', [CategoryAnalysisController::class, 'analysis']);
            });
        });

        Route::group(['prefix' => 'detail'], function () {
            Route::get('recommendations/{vendorCode}', [ProductDetailController::class, 'getRecommendation']);
            Route::get('products/{vendorCode}', [ProductDetailController::class, 'getStatistic']);
        });

        Route::group(['prefix' => 'analysis'], function () {
            Route::get('brands', [AnalysisBrandController::class, 'analysisBrand']);
            Route::get('categories', [AnalysisCategoryController::class, 'analysisCategory']);
        });

        Route::group(['prefix' => 'calls-for-action'], function () {
            Route::get('get-data', [HistoryProductController::class, 'getData']);
            Route::get('get-diagram-data/{vendorCode}', [HistoryProductController::class, 'getDiagramData']);
            Route::get('top36/{vendorCode}', [HistoryProductController::class, 'getTop36']);
        });

        Route::group(['prefix' => 'dashboard'], function () {
            Route::group(['prefix' => 'total-revenue'], function () {
                Route::post('product', [DashboardController::class, 'productRevenue']);
                Route::post('category', [DashboardController::class, 'categoryRevenue']);
                Route::post('brand', [DashboardController::class, 'brandRevenue']);
            });
            Route::group(['prefix' => 'total-ordered'], function () {
                Route::post('product', [DashboardController::class, 'productOrdered']);
                Route::post('category', [DashboardController::class, 'categoryOrdered']);
                Route::post('brand', [DashboardController::class, 'brandOrdered']);
            });
        });
    });

});

Route::group(['prefix' => 'v2'], function () {

    Route::group(['prefix' => 'wb'], function () {

        Route::group(['prefix' => 'statistic'], function () {

            Route::group(['prefix' => 'categories'], function () {
                Route::get('products', [ClickhouseCategoryController::class, 'getProducts']);
                Route::get('subcategories', [ClickhouseCategoryController::class, 'getSubcategories']);
                Route::get('price-analysis', [ClickhouseCategoryController::class, 'getPriceAnalysis']);
            });

            Route::group(['prefix' => 'brands'], function () {
                Route::get('products', [ClickhouseBrandStatisticController::class, 'getProducts']);
                Route::get('categories', [ClickhouseBrandStatisticController::class, 'getCategories']);
                Route::get('sellers', [ClickhouseBrandStatisticController::class, 'getSellers']);
                Route::get('price-analysis', [ClickhouseBrandStatisticController::class, 'getPriceAnalysis']);
            });

        });
    });

});
