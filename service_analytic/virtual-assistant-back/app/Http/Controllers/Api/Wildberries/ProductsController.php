<?php

namespace App\Http\Controllers\Api\Wildberries;

use App\Http\Requests\Common\CommonProductSegmentFilterRequest;
use App\Http\Requests\WbActivateNotActiveProducts;
use App\Http\Requests\WbDestroyRequest;
use App\Http\Requests\WbMassProductRequest;
use App\Http\Requests\WbProductRequest;
use App\Http\Requests\Wildberries\WildberriesGetPlatformProductsPriceRequest;
use App\Models\WbProduct;
use App\Repositories\Common\CommonProductDashboardRepository;
use App\Repositories\Wildberries\WildberriesPlatformProductRepository;
use App\Repositories\Wildberries\WildberriesProductRepository;
use App\Services\Analytics\WbAnalyticsService;
use App\Services\Escrow\EscrowService;
use App\Services\UserService;
use App\Services\Wildberries\WilberriesListProductsService;
use App\Services\Wildberries\WildberriesControlProductsService;
use App\Services\Wildberries\WildberriesGeneralService;
use App\Services\Wildberries\WildberriesShowProductService;
use App\Services\Wildberries\WildberriesUpdateProductService;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends CommonController
{
    // Mapper запросов с фронта сопоставляем их с базой данных при сортировке
    public $paginationPostfix = '_wildberries_products';

    /**
     * Index get list of products
     *
     * @param Request $request
     * @param WbAnalyticsService $wbAnalyticsService
     * @param WildberriesGeneralService $generalService
     * @param WilberriesListProductsService $listProductsService
     * @param WildberriesProductRepository $productRepository
     * @param WildberriesShowProductService $showProductService
     * @return JsonResponse
     */
    public function index
    (
        Request                       $request,
        WbAnalyticsService            $wbAnalyticsService,
        WildberriesGeneralService     $generalService,
        WilberriesListProductsService $listProductsService,
        WildberriesProductRepository  $productRepository,
        WildberriesShowProductService $showProductService
    ): JsonResponse {
        $params = $generalService->getParams($request);
        if (!isset($params->account['id'])) {
            return response()->api_fail(__('Not set active Wildberries account!!'), [], 403);
        }
        $products = $listProductsService->getProductList(
            $request,
            $generalService,
            $productRepository,
            $wbAnalyticsService,
            $showProductService
        );

        return response()->json($products);
    }

    /**
     * Index get one product
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show
    (
        Request                       $request,
        WbAnalyticsService            $wbAnalyticsService,
        WildberriesGeneralService     $generalService,
        WildberriesShowProductService $productService,
                                      $id
    ) {
        $params = $generalService->getParams($request);
        if (!isset($params->account['id'])) {
            return response()->api_fail(__("Can't find any user account! Please try to relogin!"), [], 403);
        }
        $status = $productService->showProduct($id, $wbAnalyticsService);
        if ($status['status'] === 'error') {
            return response()->api_fail($status['message'], [], $status['code']);
        }
        return response()->json($status['data']);
    }

    /**
     * Return escrow for product
     * @param Request $request
     * @param EscrowService $escrowService
     * @param WildberriesGeneralService $generalService
     * @param $id - productId
     * @param $nmid
     * @return JsonResponse
     */
    public function showEscrow
    (
        Request                   $request,
        EscrowService             $escrowService,
        WildberriesGeneralService $generalService,
                                  $id,
                                  $nmid
    ): JsonResponse {
        $params = $generalService->getParams($request);
        if (!isset($params->account['id'])) {
            return response()->api_fail(__("Can't find any user account! Please try to relogin!"), [], 403);
        }
        $escrow = $escrowService->getEscrowForWbProduct($id, $nmid);
        if ($escrow['status'] === 'error') {
            return response()->api_fail($escrow['message']);
        }
        return response()->json($escrow['data']);
    }

    /**
     * Store new product in database only
     * Method not use now - waiting task for create product for Wildberries
     * @param Request $request
     */
    public function store
    (
        WbProductRequest                  $request,
        WildberriesGeneralService         $generalService,
        WildberriesControlProductsService $controlProductsService
    ) {
        $params = $generalService->getParams($request);
        $request->request->add(['account_id' => $params->account['id'], 'user_id' => $params->user['id']]);
        $request->request->add(['status_id' => 4, 'status' => 'Создан']);
        $productForStore = $request->except('_token', 'user', 'account');
        $controlProductsService->store($productForStore);
    }

    /**
     * Update product in database
     * @param Request $request
     */
    public function update
    (
        $id,
        WbProductRequest $request,
        WildberriesGeneralService $generalService,
        WildberriesUpdateProductService $productService
    ) {
        $params = $generalService->getParams($request);
        if (!isset($params->account['id'])) {
            return response()->api_fail(__("Can't find any user account! Please try to relogin!"), [], 403);
        }
        $productForUpdate = $productService->getFieldsForUpdateSingle($request);
        $data = $productService->updateProduct(
            $id,
            $params->account['id'],
            $request->data,
            $request->data_nomenclatures,
            $request->nomenclatures,
            $request->data['nomenclatures'],
            $productForUpdate
        );
        if ($data['status'] === 'success') {
            return response()->json($data['data']);
        }
        return response()->api_fail($data['message'], [], 500);
    }

    /**
     * Update mass products array in database
     * @param Request $request
     */
    public function massUpdate
    (
        WbMassProductRequest            $request,
        WildberriesGeneralService       $generalService,
        WildberriesUpdateProductService $productService
    ) {
        $params = $generalService->getParams($request);
        if (!isset($params->account['id'])) {
            return response()->api_fail(__("Can't find any user account! Please try to relogin!"), [], 403);
        }
        $productForUpdate = $productService->getFieldsForUpdateMass($request);
        $userId = $request->user_id;
        $data = $productService->massUpdate($request->products, $params->account['id'], $productForUpdate, $userId);
        if ($data['status'] === 'success') {
            return response()->api_success($data['data']);
        }
        return response()->api_fail($data['message']);
    }

    /**
     * Product send to Wildberries
     * @param $id
     * @return JsonResponse|null
     */
    public function sync
    (
        $id,
        Request $request,
        WildberriesGeneralService $generalService,
        WildberriesUpdateProductService $updateProductService
    ) {
        $params = $generalService->getParams($request);
        $status = $updateProductService->sync($id, $params->account['id']);
        return $status ? response()->json(['ok']) : null;
    }

    /**
     * Удаление товара - удаляем товар не на маркет плейсе, а только "мягкое удаление" в базе магазина
     * @param WbDestroyRequest $request
     * @param WildberriesControlProductsService $controlProductsService
     * @return mixed
     */
    public function destroy
    (
        WbDestroyRequest                  $request,
        WildberriesControlProductsService $controlProductsService
    ) {
        $status = $controlProductsService->destroy($request->ids);
        if ($status['status'] === 'error') {
            return response()->api_fail($status['message'], [], $status['code']);
        }
        return response()->api_success($status['data']);
    }

    /**
     * Product get from Wildberries
     * @param $id
     * @param Request $request
     */
    public function resync
    (
        $id,
        Request $request,
        WildberriesGeneralService $generalService,
        WildberriesControlProductsService $controlProductsService
    ): ?JsonResponse {
        $params = $generalService->getParams($request);
        return $controlProductsService->resync($params->client, $params->account['id'], $id);
    }

    /**
     * Searching none-active products of Brand
     *
     * @param Request $request
     * @param WildberriesGeneralService $generalService
     * @param WildberriesProductRepository $wildberriesProductRepository
     * @return mixed
     */
    public function selectNotActiveBrands
    (
        Request                      $request,
        WildberriesGeneralService    $generalService,
        WildberriesProductRepository $wildberriesProductRepository,
    ): mixed {
        $params = $generalService->getParams($request);
        if (!isset($params->account['id'])) {
            return response()->api_fail(__("Can't find any user account! Please try to relogin!"), [], 403);
        }
        return $wildberriesProductRepository
            ->selectNotActiveBrandsWithFilters(
                $request->search ?? '',
                $params->account['id'],
                UserService::forbiddenBrands() ?? [],
                (int)$request->availability
            );
    }

    /**
     * Searching none-active products
     *
     * @param Request $request
     * @param WildberriesGeneralService $generalService
     * @param WildberriesProductRepository $wildberriesProductRepository
     * @return mixed
     */
    public function selectNotActiveProducts
    (
        Request                      $request,
        WildberriesGeneralService    $generalService,
        WildberriesProductRepository $wildberriesProductRepository,
    ): mixed {
        $params = $generalService->getParams($request);
        if (!isset($params->account['id'])) {
            return response()->api_fail(__("Can't find any user account! Please try to relogin!"), [], 403);
        }
        return $wildberriesProductRepository
            ->selectNotActiveProductsByTextAndBrand(
                $request->search ?? '',
                $request->brand ?? '',
                $params->account['id'],
                UserService::forbiddenBrands() ?? [],
                (int)$request->availability
            );
    }

    /**
     * Добавление предварительно загруженных товаров
     * @param WbActivateNotActiveProducts $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|JsonResponse|\Illuminate\Http\Response
     */
    public function activateNotActiveProducts
    (
        WbActivateNotActiveProducts       $request,
        WildberriesControlProductsService $controlProductsService,
    ) {
        $max = UserService::getMaxProductsCount();
        if (WbProduct::currentUserAndAccount()->count() > ($max + count($request->get('ids')))) {
            return response()->api_fail("Вы можете добавить максимально $max продукта(ов) на одну платформу!", [], 403);
        }
        $status = $controlProductsService->activateNotActiveProducts($request->get('ids'));
        if ($status['status'] === 'error') {
            return response()->api_fail($status['message'], [], $status['code']);
        }
        return response()->json($status['data']);
    }

    /**
     * @param CommonProductSegmentFilterRequest $productSegmentFilterRequest
     * @param WildberriesProductRepository $productRepository
     * @param CommonProductDashboardRepository $dashboardRepository
     * @param WilberriesListProductsService $listProductsService
     * @return mixed
     * @throws \Exception
     */
    public function getProductsBySegmentFilter(
        CommonProductSegmentFilterRequest $productSegmentFilterRequest,
        WildberriesProductRepository $productRepository,
        CommonProductDashboardRepository $dashboardRepository,
    ) {
        $query = $productSegmentFilterRequest->get('query');

        $dashboard = $dashboardRepository->getAccountDashboardByType(
            UserService::getUserId(),
            UserService::getAccountId(),
            UserService::getCurrentAccountPlatformId(),
            $query['dashboard_type']
        );

        $products = $productRepository->getProductsQueryByDashboardSegmentation(
            $dashboard,
            $query['segment_type'],
            $productSegmentFilterRequest->get('sortBy'),
            $productSegmentFilterRequest->get('sortType'),
        );

        if (!$products) {
            return response()->api_success([]);
        }

        $appends = [
            'sortBy' => $productSegmentFilterRequest->get('sortBy'),
            'sortType' => $productSegmentFilterRequest->get('sortType'),
        ];

        $products = PaginatorHelper::addPagination($productSegmentFilterRequest, $products)
            ->setPath('')
            ->appends($appends);

        return response()->api_success($products);
    }

    /**
     * @param WildberriesGetPlatformProductsPriceRequest $getPlatformPriceRequest
     * @param WildberriesPlatformProductRepository $platformProductRepository
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPlatformProductsPrice(
        WildberriesGetPlatformProductsPriceRequest $getPlatformPriceRequest,
        WildberriesPlatformProductRepository $platformProductRepository
    ): mixed {
        $query = $getPlatformPriceRequest->get('query');
        $productPrices = [];

        if (isset($query['from_file']) && $query['from_file']) {
            $productPrices = $platformProductRepository->getProductPricesFromNmidsFromFile(
                UserService::getAccountId(),
                $query['nmid']
            );
        } else {
            $productPrices = $platformProductRepository->getProductPricesByNmid($query['nmid']);
        }

        if (!$productPrices) {
            return response()->api_fail([], [], Response::HTTP_NOT_FOUND);
        }

        return response()->api_success($productPrices);
    }
}
