<?php

namespace App\Http\Controllers\Frontend\Words;

use App\Actions\Words\WordsSaveFromXlsAction;
use App\Actions\Words\WordsSaveFromAutoselectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Words\WordsSaveFromXlsRequest;
use App\Http\Requests\V1\Words\WordsSaveFromAutoselectRequest;
use App\DataTransferObjects\Frontend\Words\WordsSaveFromXlsRequestData;
use App\DataTransferObjects\Frontend\Words\WordsSaveFromAutoselectRequestData;
use Illuminate\Http\JsonResponse;

/**
 * Class WordsController
 *
 * @package App\Http\Controllers\Frontend\Words
 */
class WordsController extends Controller
{
    /**
     * @param WordsSaveFromXlsRequest $request
     * @return JsonResponse
     */
    public function saveFromXls(WordsSaveFromXlsRequest $request): JsonResponse
    {
        $requestData = WordsSaveFromXlsRequestData::fromRequest($request);
        $response = (new WordsSaveFromXlsAction())->run($requestData);
        return response()->json(
            [
                'success' => empty($response['errors']),
                'data'    => $response,
                'errors'  => $response['errors']
            ]
        );
    }

    /**
     * @param WordsSaveFromAutoselectRequest $request
     * @return JsonResponse
     */
    public function saveFromAutoselect(WordsSaveFromAutoselectRequest $request):JsonResponse
    {
        $requestData = WordsSaveFromAutoselectRequestData::fromRequest($request);
        $response = (new WordsSaveFromAutoselectAction())->run($requestData);
        return response()->json(
            [
                'success' => empty($response['errors']),
                'data'    => $response,
                'errors'  => $response['errors']
            ]
        );
    }
}
