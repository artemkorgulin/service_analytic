<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ServiceShowRequest;

use App\Models\Service;

use App\Repositories\Billing\ServiceRepository;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ServiceRepository $serviceRepository)
    {
        return response()->api_success(
            $serviceRepository
                ->getVisible()
                ->get()
                ->map([$serviceRepository, 'toArray'])
        );
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceShowRequest $request, ServiceRepository $serviceRepository)
    {
        return response()->api_success(
            $serviceRepository->toArray(
                $serviceRepository->find($request->id)
            )
        );
    }
}
