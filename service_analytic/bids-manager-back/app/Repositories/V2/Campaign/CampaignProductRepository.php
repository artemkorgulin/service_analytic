<?php


namespace App\Repositories\V2\Campaign;

use App\Models\CampaignProduct as Model;
use App\Repositories\BaseRepository;
use App\Models\Status;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CampaignProductRepository
 *
 * @package App\Repositories\V2\Campaign
 */
class CampaignProductRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    /**
     * @param int $campaignId
     * @param int $productId
     * @return Model|null
     */
    public function getItemByRelation(int $campaignId, int $productId)
    {
        $result = $this->startConditions()
            ->where('campaign_id', $campaignId)
            ->where('product_id', $productId)
            ->first();
        return $result;
    }

    /**
     * @param int $campaignId
     * @return Collection
     */
    public function getListCondition(string $fieldName, int $id): Collection
    {
        return $this->startConditions()
            ->select([
                'campaign_products.id',
                'campaign_products.group_id',
                'groups.name as group_name',
                'campaign_products.created_at'
            ])->where([
                ['campaign_products.' . $fieldName, $id],
                ['campaign_products.status_id', '!=', Status::ARCHIVED],
                ['campaign_products.status_id', '!=', Status::DELETED]
            ])->leftJoin('groups', 'groups.id', '=', 'campaign_products.group_id')
            ->withCount([
                'stopWords as stop_words_count' => function ($query) {
                    $query->select(DB::raw('count(distinct(stop_word_id))'));
                },
                'keywords as keywords_count' => function ($query) {
                    $query->where([
                        ['status_id', '!=', Status::ARCHIVED],
                        ['status_id', '!=', Status::DELETED],
                    ])->select(DB::raw('count(distinct(keyword_id))'));
                }
            ])
            ->groupBy('campaign_products.id')
            ->get();
    }

    /**
     * @param int $campaignId
     * @return Collection
     */
    public function getListByCampaignId(int $campaignId)
    {
        return $this->getListCondition('campaign_id', $campaignId);
    }


    /**
     * @param int $groupId
     * @return Collection
     */
    public function getListByGroupId(int $groupId)
    {
        return $this->getListCondition('group_id', $groupId);
    }
}
