<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PromocodeApplyRequest;
use App\Models\Promocode;
use App\Repositories\Billing\PromocodeRepository;
use App\Services\Billing\PromocodeService;

class PromocodeController extends Controller
{
    /**
     * Получить информацию по сохранённым промокодам у пользователя
     * @return mixed
     */
    public function index(PromocodeRepository $promocodeRepository)
    {
        return response()->api_success(
            $promocodeRepository
                ->savedFor(auth()->user())
                ->map([$promocodeRepository, 'toArray'])
        );
    }

    /**
     * Добавить промокод человеку
     * @return mixed
     */
    public function applyCode(PromocodeRepository $promocodeRepository, PromocodeService $promocodeService)
    {
        return ModelHelper::transaction(
            function () use($promocodeRepository, $promocodeService) {
                $request = App::make(PromocodeApplyRequest::class);

                $promocode = $promocodeRepository->getByCode($request->code);
                $promocodeService->addToUser(
                    auth()->user(),
                    $promocode
                );

                return response()->api_success(
                    $promocodeRepository->toArray($promocode)
                );
            }
        );
    }
}
