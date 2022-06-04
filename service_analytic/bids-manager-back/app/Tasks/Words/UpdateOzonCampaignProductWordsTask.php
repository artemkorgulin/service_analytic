<?php

namespace App\Tasks\Words;

use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Helpers\OzonHelper;
use App\Repositories\V2\Campaign\CampaignProductRepository;
use App\Services\OzonPerformanceService;
use App\Tasks\Task;
use App\Repositories\Frontend\Campaign\CampaignRepository;

/**
 * Обновить ключевые и минус слова для товара РК
 *
 * Class UpdateOzonCampaignProductWordsTask
 *
 * @package App\Tasks\Words
 */
class UpdateOzonCampaignProductWordsTask extends Task
{
    /** @var CampaignRepository */
    protected $campaignRepository;

    /** @var CampaignProductRepository */
    protected $campaignProductRepository;

    /**
     * UpdateOzonCampaignProductWordsTask constructor.
     */
    public function __construct()
    {
        $this->campaignRepository = new CampaignRepository();
        $this->campaignProductRepository = new CampaignProductRepository();
    }

    /**
     * @param int $campaignProductId
     * @return bool
     */
    public function run(int $campaignProductId): bool
    {
        /** @var CampaignProduct $campaignProduct */
        $campaignProduct = $this->campaignProductRepository->getItem($campaignProductId);
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepository->getItem($campaignProduct->campaign_id);
        if ($campaign->ozon_id) {
            (new OzonHelper)->ozonUpdateWords($campaign);
        }
        return OzonPerformanceService::getLastError() && $campaign->ozon_id;
    }
}
