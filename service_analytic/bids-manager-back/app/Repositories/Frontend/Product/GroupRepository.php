<?php


namespace App\Repositories\Frontend\Product;

use App\Models\Group;
use App\Repositories\BaseRepository;
use App\Models\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GroupRepository
 *
 * @package App\Repositories\Frontend\Product
 */
class GroupRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Group::class;
    }

    /**
     * @param $campaignId
     * @return Collection
     */
    public function getCampaignGroups($campaignId)
    {
        $result = $this->startConditions()
            ->select()
            ->where([
                ['campaign_id', $campaignId],
                ['status_id', '!=', Status::DELETED]
            ])
            ->selectRaw('(CASE WHEN name IS NULL THEN ozon_id ELSE name END) AS name')
            ->get();
        return $result;
    }
}
