<?php

namespace App\Http\Controllers;

use App\Classes\WbKeyRequestHandler;
use App\Http\Requests\OzWbSearchRequest;
use App\Http\Requests\WbPickListRequest;
use App\Models\PlatfomSemantic;
use App\Models\PlatformSemanticsRufago;
use App\Models\WbPickList;
use App\Models\WbPickListProduct;
use App\Models\WbUsingKeyword;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WbPickListController extends Controller
{
    public const COUNT_RESPONSE = 60;

    public function setRequest(Request $request, WbKeyRequestHandler $wbKeyRequestHandler){
        $this->user = $request->get('user');
        $this->page = abs((int)$request->page) ?: 1;
        if (app()->runningInConsole() === false && $this->user) {
            if ($request->perPage) {
                $this->pagination = (int)$request->perPage;
                Cache::put($this->user['id'] . $this->paginationPostfix, $this->pagination);
            } elseif (Cache::has($this->user['id'] . $this->paginationPostfix)) {
                $this->pagination = Cache::get($this->user['id'] . $this->paginationPostfix);
            }
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request, WbKeyRequestHandler $wbKeyRequestHandler)
    {
        $this->setRequest($request, $wbKeyRequestHandler);
        try {
            $productId = $request->input('product_id');

            $wbPickListProduct = WbPickListProduct::where('wb_product_id', $productId)->get();

            if (!$wbPickListProduct->count()) {
                try {
                    $wbKeyRequestHandler->handle($productId);

                    $wbPickListProduct = WbPickListProduct::where('wb_product_id', $productId)->get();
                } catch (Exception $exception) {
                    report($exception);
                    $wbPickListProduct = [];
                }
            }

            $result = $wbPickListProduct;

            /*$wbPickList = WbPickList::where('wb_product_id', $productId)->get();

            if (!$wbPickList->count()) {
                $wbPickListProduct = WbPickListProduct::where('wb_product_id', $productId)->get();

                if (!$wbPickListProduct->count()) {
                    $wbKeyRequestHandler->handle($productId);

                    $wbPickListProduct = WbPickListProduct::where('wb_product_id', $productId)->get();
                }

                $result = $wbPickListProduct;
            } else {
                $result = $wbPickList;
            }*/

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return response()->api_fail('code - '.$exception->getCode().', message - '.$exception->getMessage(), [],
                500);
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request, WbKeyRequestHandler $wbKeyRequestHandler)
    {
        $this->setRequest($request, $wbKeyRequestHandler);
        $data = $request->input('data');
        $productId = $request->input('product_id');

        try {
            if ($request->input('delete_old')) {
                WbPickList::where('wb_product_id', $productId)->delete();
            }
            foreach ($data as $element) {
                WbPickList::create([
                    'wb_product_id' => $productId,
                    'name' => $element['name'],
                    'popularity' => $element['popularity'],
                ]);
            }

            return response()->api_success([]);
        } catch (Exception $exception) {
            report($exception);
            return response()->api_fail('code - '.$exception->getCode().', message - '.$exception->getMessage(), [],
                500);
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function getPickList(Request $request, WbKeyRequestHandler $wbKeyRequestHandler)
    {
        $this->setRequest($request, $wbKeyRequestHandler);
        $productId = $request->input('product_id');

        $wbPickList = WbPickList::where('wb_product_id', $productId)->get();

        if ($wbPickList->isEmpty()) {
            $wbPickList = WbUsingKeyword::where('wb_product_id', $productId)->get();
        }

        return response()->api_success($wbPickList);
    }

    /**
     * @param  WbPickListRequest  $request
     * @return mixed
     */
    public function destroy(WbPickListRequest $request, WbKeyRequestHandler $wbKeyRequestHandler)
    {
        $this->setRequest($request, $wbKeyRequestHandler);
        if ($request->input('id')) {
            WbPickList::where('id', $request->input('id'))->delete();
        } else {
            WbPickList::where('wb_product_id', $request->input('wb_product_id'))
                ->where('name', $request->input('name'))
                ->delete();
        }

        return response()->api_success([]);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function getKeyRequests(OzWbSearchRequest $request, WbKeyRequestHandler $wbKeyRequestHandler)
    {
        $this->setRequest($request, $wbKeyRequestHandler);
        try {
            $platfomSemantics = PlatformSemanticsRufago::query()
                ->select('search_response as name', DB::raw('max(popularity) as popularity'))
                ->where('wb_parent_name', 'like', '%' . $request->get('category') . '%')
                ->whereIn('wb_product_name', $request->get('products'))
                ->groupBy('name')
                ->orderByDesc('popularity')
                ->limit(self::COUNT_RESPONSE * count($request->get('products')));

            $result = $platfomSemantics->get();

            if (count($result->toArray()) === 1 && $result->toArray()[0]['name'] === "0") {
                $result = [];
            }

            return response()->api_success($result);
        } catch (Exception $exception) {
            report($exception);
            return response()->api_fail('Ошибка получения search_response wb', [], 500);
        }
    }
}
