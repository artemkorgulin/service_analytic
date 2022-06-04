<?php

namespace App\Http\Controllers\Frontend\Autoselect;


use App\Actions\Autoselect\GetFilteredAutoselectResultsAction;
use App\Actions\Autoselect\GetXLSKeywordUploadTemplateAction;
use App\Actions\Autoselect\RunAutoselectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Autoselect\AutoselectRunRequest;
use App\Http\Requests\V1\Autoselect\AutoselectGetResultListRequest;
use App\DataTransferObjects\Frontend\Autoselect\AutoselectParametersRequestData;
use App\DataTransferObjects\Frontend\Autoselect\AutoselectResultsRequestData;
use App\Http\Requests\V1\Autoselect\AutoselectGetXLSResultsRequest;
use App\DataTransferObjects\Frontend\Autoselect\AutoselectXLSResultsRequestData;
use App\Actions\Autoselect\GetAutoselectResultsXLSAction;
use App\Services\VirtualAssistantService;
use Illuminate\Http\JsonResponse;

/**
 * Class AutoselectController
 *
 * @package App\Http\Controllers\Frontend\Autoselect
 */
class AutoselectController extends Controller
{
    /**
     * Запустить автоподбор
     *
     * @param AutoselectRunRequest $request
     * @return JsonResponse
     */
    public function run(AutoselectRunRequest $request): JsonResponse
    {
        $requestData = AutoselectParametersRequestData::fromRequest($request);
        $autoselectResult = (new RunAutoselectAction)->run($requestData);

        return response()->json(
            [
                'success' => true,
                'data'    => $autoselectResult,
                'errors'  => [
                    VirtualAssistantService::getLastError()
                ]
            ]
        );
    }

    /**
     * @param AutoselectGetResultListRequest $request
     * @return JsonResponse
     */
    public function getResultList(AutoselectGetResultListRequest $request): JsonResponse
    {
        $requestData = AutoselectResultsRequestData::fromRequest($request);
        $autoselectResultList = (new GetFilteredAutoselectResultsAction())->run($requestData);

        return response()->json(
            [
                'success' => true,
                'data'    => $autoselectResultList,
                'errors'  => []
            ]
        );
    }

    /**
     * @param AutoselectGetXLSResultsRequest $request
     * @return JsonResponse
     */
    public function getXLSResults(AutoselectGetXLSResultsRequest $request):JsonResponse
    {
        $requestData = AutoselectXLSResultsRequestData::fromRequest($request);
        $res = (new GetAutoselectResultsXLSAction())->run($requestData);

        return response()->json(
            [
                'success' => !empty($res),
                'data'    => $res,
                'errors'  => []
            ]
        );
    }

    /**
     * @return JsonResponse
     */
    public function getXLSKeywordUploadTemplate():JsonResponse
    {
        $res = (new GetXLSKeywordUploadTemplateAction)->run();
        return response()->json(
            [
                'success' => !empty($res),
                'data'    => $res,
                'errors'  => []
            ]
        );
    }
}
