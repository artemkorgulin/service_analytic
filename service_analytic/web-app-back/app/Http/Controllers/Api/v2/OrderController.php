<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BillingCheckPriceRequest;
use App\Http\Requests\Api\BillingOrderRequest;
use App\Http\Requests\Api\OrderShowRequest;

use App\Http\Resources\OrderResource;
use App\Repositories\Billing\OrderRepository;

use App\Models\Order;

use App\Services\CalculateService;
use App\Services\CompanyService;
use App\Services\Billing\OrderService;

use App\Services\Billing\PaymentService;

class OrderController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(OrderRepository $orderRepository)
    {
        return response()->api_success(
            $orderRepository
                ->getByUser(auth()->user())
                ->latest()
                ->paginate(20)
                ->map([$orderRepository, 'toArray'])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\Api\BillingOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BillingOrderRequest $request, OrderService $orderService)
    {
        return response()->api_success(
            $orderService->createOrderForRequest($request),
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(OrderShowRequest $request, OrderRepository $orderRepository)
    {
        return response()->api_success(
            $orderRepository->toArray(
                $orderRepository->find($request->id)
            )
        );
    }

    /**
     * Проверить цену у заказа перед формированием
     */
    public function checkFinalPrice(CalculateService $calculateService, BillingCheckPriceRequest $request)
    {
        return response()->api_success(
            $calculateService->priceCalculationForBillingOrderRequest($request)
        );
    }
}
