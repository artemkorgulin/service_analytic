<?php

namespace App\Http\Controllers\Api\V2\Campaign;

use App\Http\Requests\V2\Keyword\CampaignKeywordDestroyRequest;
use App\Http\Requests\V2\Keyword\CampaignKeywordsStoreRequest;
use App\Http\Requests\V2\Keyword\KeywordGetListByFilterRequest;
use App\Models\Campaign;
use App\Models\CampaignKeyword;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Keyword\CampaignKeywordUpdateRequest;
use App\Repositories\Frontend\Keyword\CampaignKeywordRepository;
use Illuminate\Http\JsonResponse;

/**
 * Campaign products or groups keywords controller
 */
class KeywordController extends Controller
{
    /**
     * Get keywords
     *
     * @param  KeywordGetListByFilterRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function index(KeywordGetListByFilterRequest $request, Campaign $campaign): JsonResponse
    {
        $campaignKeywordRepository = new CampaignKeywordRepository();
        $campaignKeywords = $campaignKeywordRepository->getCampaignKeywords($request, $campaign);

        return response()->api(true, $campaignKeywords, []);
    }

    /**
     * Create and attach keywords
     *
     * @param  CampaignKeywordsStoreRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function store(CampaignKeywordsStoreRequest $request, Campaign $campaign)
    {
        $missedKeywords = CampaignKeyword::saveKeywords($request->input('keywords'));

        return response()->api(true, ['missed_keywords' => $missedKeywords], []);
    }

    /**
     * Update keywords bids
     *
     * @param  CampaignKeywordUpdateRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function updateBids(CampaignKeywordUpdateRequest $request, Campaign $campaign)
    {
        $updateCount = CampaignKeyword::whereIn('id', $request->keyword_ids)->update(['bid' => $request->bid]);

        return response()->api(true, ['updated_count' => $updateCount], []);
    }

    /**
     * Detach keywords
     *
     * @param  CampaignKeywordDestroyRequest  $request
     * @param  Campaign  $campaign
     * @return mixed
     */
    public function destroy(CampaignKeywordDestroyRequest $request, Campaign $campaign)
    {
        $updated = CampaignKeyword::detachKeywords($request->all(), $campaign);

        return response()->api(true, ['keyword_ids' => $request->keyword_ids, 'updated_count' => $updated], []);
    }
}
