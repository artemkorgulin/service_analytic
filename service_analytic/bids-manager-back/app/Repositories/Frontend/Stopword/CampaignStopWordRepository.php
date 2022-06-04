<?php


namespace App\Repositories\Frontend\Stopword;

use App\Models\Campaign;
use App\Models\CampaignStopWord as Model;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Class CampaignStopWordRepository
 *
 * @package App\Repositories\Frontend\Keyword
 */
class CampaignStopWordRepository extends BaseRepository
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
    public function getCampaignStopWords(Request $request, Campaign $campaign)
    {
        $groupId = $request->get('group_id') ?? null;
        $productId = $request->get('campaign_product_id') ?? null;

        /** @var Collection $result */
        $result = $this->startConditions()
            ->select([
                'stop_words.id',
                'campaign_stop_words.campaign_product_id',
                'campaign_stop_words.group_id',
                'stop_words.name',
            ])
            ->where('cg.campaign_id', '=', $campaign->id)
            ->join('stop_words', 'campaign_stop_words.stop_word_id', '=', 'stop_words.id')
            ->orderBy('stop_words.name', 'ASC');

        if (!empty($groupId)) {
            $result->where('campaign_stop_words.group_id', '=', $groupId)
                ->groupBy([
                    'id',
                    'name',
                    'campaign_stop_words.group_id',
                    'campaign_stop_words.campaign_product_id'
                ])
                ->join('campaign_products AS cg', function ($join) {
                    $join->on('campaign_stop_words.campaign_product_id', '=', 'cg.id');
                    $join->orOn('campaign_stop_words.group_id', '=', 'cg.group_id');
                });
        } else {
            $result->join('campaign_products AS cg', 'campaign_stop_words.campaign_product_id', '=', 'cg.id');
        }

        if (!empty($productId)) {
            $result->where('campaign_stop_words.campaign_product_id', '=', $productId);
        }

        return $result->get();
    }

    public function getByRelation($campaignProductId, $stopwordId)
    {
        $result = $this->startConditions()
            ->select()
            ->where('campaign_product_id', $campaignProductId)
            ->where('stop_word_id', $stopwordId)
            ->first();
        return $result;
    }

    /**
     * @param int $campaignProductId
     * @return int
     */
    public function getUsableStopwordsCount(int $campaignProductId): int
    {
        $result = $this->startConditions()
            ->where('campaign_product_id', $campaignProductId)
            ->distinct()
            ->count('stop_word_id');
        return $result;
    }
}
