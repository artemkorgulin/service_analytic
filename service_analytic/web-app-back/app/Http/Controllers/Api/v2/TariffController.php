<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TariffShowRequest;
use App\Repositories\Billing\TariffRepository;

use App\Models\Tariff;

class TariffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(TariffRepository $tariffRepository)
    {
        return response()->api_success(
            $tariffRepository
                ->getVisible()
                ->get()
                ->map([$tariffRepository, 'toArray'])
        );
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(TariffShowRequest $request, TariffRepository $tariffRepository)
    {
        return response()->api_success(
            $tariffRepository->toArray(
                $tariffRepository->find($request->id)
            )
        );
    }
}
