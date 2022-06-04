<?php


namespace App\Repositories\Frontend\Campaign;

use App\Models\CampaignProduct as Model;
use App\Repositories\BaseRepository;
use App\Models\Status;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CampaignProductRepository
 *
 * @package App\Repositories\Frontend\Campaign
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
    public function getListByCampaignId(int $campaignId)
    {
        $columns = [
            'campaign_products.id',
            'campaign_products.group_id',
            'groups.name as group_name',
            'products.name',
            'products.id as product_id',
            'products.sku',
            'campaign_products.created_at',
            'categories.name as category_name'
        ];

        return $this->startConditions()
            ->select($columns)
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->whereNull('campaign_products.group_id')
            ->where([
                ['campaign_products.campaign_id', $campaignId],
                ['campaign_products.status_id', '!=', Status::ARCHIVED],
                ['campaign_products.status_id', '!=', Status::DELETED],
                ['products.visible', 1],
            ])
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('groups', 'groups.id', '=', 'campaign_products.group_id')
            ->withCount([
                'stopWords as stop_words_count' => function ($query) {
                    $query->select(DB::raw('count(distinct(stop_word_id))'));
                },
                'keywords as keywords_count' => function ($query) {
                    $query->where([
                        ['status_id', '!=', Status::ARCHIVED],
                        ['status_id', '!=', Status::DELETED],
                    ])
                        ->select(DB::raw('count(distinct(keyword_id))'));
                }
            ])
            ->groupBy('campaign_products.id')
            ->get();
    }


    /**
     * @param int $groupId
     * @return Collection
     */
    public function getListByGroupId(int $groupId)
    {
        $columns = [
            'groups.id',
            'campaign_products.id',
            'campaign_products.group_id',
            'groups.name as group_name',
            'products.name',
            'products.id as product_id',
            'products.sku',
            'campaign_products.created_at',
            'categories.name as category_name'
        ];

        return $this->startConditions()
            ->select($columns)
            ->join('products', 'products.id', '=', 'campaign_products.product_id')
            ->where([
                ['campaign_products.group_id', $groupId],
                ['campaign_products.status_id', '!=', Status::ARCHIVED],
                ['campaign_products.status_id', '!=', Status::DELETED],
                ['products.visible', 1],
            ])
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('groups', 'groups.id', '=', 'campaign_products.group_id')
            ->withCount([
                'stopWords as stop_words_count' => function ($query) {
                    $query->select(DB::raw('count(distinct(stop_word_id))'));
                },
                'keywords as keywords_count' => function ($query) {
                    $query->where([
                        ['status_id', '!=', Status::ARCHIVED],
                        ['status_id', '!=', Status::DELETED],
                    ])
                        ->select(DB::raw('count(distinct(keyword_id))'));
                }
            ])
            ->groupBy('campaign_products.id')
            ->get();
    }

    /**
     * @param int $campaignId
     * @return Collection
     */
    public function getProductCollectionByCampaignId(int $campaignId): Collection
    {
        $result = $this->startConditions()
            ->select()
            ->where('campaign_id', $campaignId)
            ->get();

        return $result;
    }


    /**
     * @param int $groupId
     * @return Collection
     */
    public function getGroupProducts(int $groupId): Collection
    {
        $result = $this->startConditions()
            ->where('group_id', $groupId)
            ->get();
        return $result;
    }

    /**
     * @param int $campaignId
     * @param int $groupId
     * @return Collection
     */
    public function getCampaignGroupProducts(int $campaignId, int $groupId): Collection
    {
        $result = $this->startConditions()
            ->where('campaign_id', $campaignId)
            ->where('group_id', $groupId)
            ->get();
        return $result;
    }
}
