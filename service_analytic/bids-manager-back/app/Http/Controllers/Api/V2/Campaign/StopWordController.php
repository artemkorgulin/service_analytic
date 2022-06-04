<?php

namespace App\Http\Controllers\Api\V2\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Stopword\CampaignStopWordDestroyRequest;
use App\Http\Requests\V2\Stopword\CampaignStopWordStoreRequest;
use App\Http\Requests\V2\Stopword\StopWordGetListByFilterRequest;
use App\Models\Campaign;
use App\Models\CampaignStopWord;
use App\Repositories\Frontend\Stopword\CampaignStopWordRepository;
use Illuminate\Http\JsonResponse;

/**
 * Campaign products or groups stop words controller
 */
class StopWordController extends Controller
{
    /**
     * @param  StopWordGetListByFilterRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function index(StopWordGetListByFilterRequest $request, Campaign $campaign): JsonResponse
    {
        $campaignStopWordRepository = new CampaignStopWordRepository();
        $campaignStopWords = $campaignStopWordRepository->getCampaignStopWords($request, $campaign);

        return response()->api(true, $campaignStopWords, []);
    }

    /**
     * @param  CampaignStopWordStoreRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function store(CampaignStopWordStoreRequest $request, Campaign $campaign): JsonResponse
    {
        $resultStopWords = CampaignStopWord::saveStopWords($request->input('stop_words'), $campaign);

        return response()->api(true, $resultStopWords, []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CampaignStopWordDestroyRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function destroy(CampaignStopWordDestroyRequest $request, Campaign $campaign): JsonResponse
    {
        $data = CampaignStopWord::detachStopWords($request->all(), $campaign);

        return response()->api(true, $data, []);
    }
}
