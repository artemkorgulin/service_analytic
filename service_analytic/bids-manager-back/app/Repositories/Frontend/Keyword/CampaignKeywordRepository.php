<?php


namespace App\Repositories\Frontend\Keyword;

use App\Models\Campaign;
use App\Models\CampaignKeyword as Model;
use App\Repositories\BaseRepository;
use App\Models\Status;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Class CampaignKeywordRepository
 *
 * @package App\Repositories\Frontend\Keyword
 */
class CampaignKeywordRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    /**
     * @param  Request  $request
     * @param  Campaign  $campaign
     * @return Collection
     */
    public function getCampaignKeywords(Request $request, Campaign $campaign)
    {
        $groupId = $request->get('group_id') ?? null;
        $productId = $request->get('campaign_product_id') ?? null;

        $lastPopularities = DB::table('keyword_popularities')
            ->select(DB::raw('keyword_id, MAX(popularity) as popularity'))
            ->groupBy('keyword_id')
            ->orderByDesc('popularity');


        /** @var Collection $result */
        $result = $this->startConditions()
            ->whereNotIn('campaign_keywords.status_id', [Status::DELETED, Status::ARCHIVED])
            ->where('cg.campaign_id', '=', $campaign->id)
            ->join('keywords', 'campaign_keywords.keyword_id', '=', 'keywords.id')
            ->leftJoinSub(
                $lastPopularities,
                'last_keyword_popularities',
                function($join) {
                    $join->on('last_keyword_popularities.keyword_id', '=', 'keywords.id');
                }
            )
            ->orderBy('campaign_keywords.keyword_id', 'ASC');

        if (!empty($groupId)) {
            $result->where('campaign_keywords.group_id', '=', $groupId)
                ->groupBy(['keyword_id', 'campaign_keywords.id', 'name', 'campaign_keywords.group_id'])
                ->select([
                    'keywords.id as keyword_id',
                    'campaign_keywords.id',
                    'keywords.name as name',
                    'campaign_keywords.group_id',
                    DB::raw('SUM(campaign_keywords.bid) AS bid'),
                    DB::raw('MAX(last_keyword_popularities.popularity) AS popularity')
                ])->join('campaign_products AS cg', function ($join) {
                    $join->on('campaign_keywords.campaign_product_id', '=', 'cg.id');
                    $join->orOn('campaign_keywords.group_id', '=', 'cg.group_id');
                });
        } else {
            $result->select([
                'keywords.id as keyword_id',
                'campaign_keywords.id',
                'keywords.name as name',
                'campaign_keywords.status_id',
                'campaign_keywords.campaign_product_id',
                'campaign_keywords.group_id',
                'campaign_keywords.bid',
                'last_keyword_popularities.popularity'
            ])->join('campaign_products AS cg', 'campaign_keywords.campaign_product_id', '=', 'cg.id');
        }

        if (!empty($productId)) {
            $result->where('campaign_keywords.campaign_product_id', '=', $productId);
        }

        return $result->get();
    }


    /**
     * @param $productId
     */
    public function getCampaignProductKeywords($productId)
    {
        $columns = [
            'campaign_keywords.id',
            'campaign_keywords.bid',
            'keywords.name'
        ];
        $result = $this->startConditions()
            ->select($columns)
            ->where('campaign_product_id', $productId)
            ->leftJoin('keywords', 'keywords.id', '=', 'campaign_keywords.keyword_id')
            ->groupBy('keywords.id')
            ->orderBy('keywords.name', 'ASC')
            ->get();
        return $result;
    }

    public function getByRelation($campaignProductId, $keywordId)
    {
        $result = $this->startConditions()
            ->select()
            ->where('campaign_product_id', $campaignProductId)
            ->where('keyword_id', $keywordId)
            ->first();
        return $result;
    }

    /**
     * @param int $campaignProductId
     * @return int
     */
    public function getUsableKeywordsCount(int $campaignProductId): int
    {
        $result = $this->startConditions()
            ->where('campaign_product_id', $campaignProductId)
            ->whereNotIn('status_id', [Status::ARCHIVED, Status::DELETED])
            ->distinct()
            ->count('keyword_id');
        return $result;
    }
}
