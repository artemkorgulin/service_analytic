<?php


namespace App\Repositories\Frontend\Campaign;

use App\Models\Campaign as Model;
use App\Repositories\BaseRepository;

/**
 * Class CampaignRepository
 *
 * @package App\Repositories\Frontend\Campaign
 */
class CampaignRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Model::class;
    }
}
