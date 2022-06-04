<?php

namespace App\Tasks\Products;

use App\Models\CampaignProduct;
use App\Repositories\V2\Campaign\CampaignProductRepository;
use App\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GetGroupedProductsTask
 *
 * @package App\Tasks\Products
 */
class GetGroupedProductsTask extends Task
{

    /** @var CampaignProductRepository $campaignProductRepository */
    protected $campaignProductRepository;

    /**
     * GetGroupedProductsTask constructor.
     */
    public function __construct()
    {
        $this->campaignProductRepository = new CampaignProductRepository();
    }

    /**
     * Возвращает коллекцию всех товаров в группе,
     * принадлежащей входному товару рекламной кампании
     *
     * @param int $campaignProductId
     * @return Collection
     */
    public function run(int $campaignProductId)
    {
        /** @var CampaignProduct $campaignProduct */
        $campaignProduct = $this->campaignProductRepository->getItem($campaignProductId);
        if (!is_null($campaignProduct->group_id)) {
            $campaignProducts = $this->campaignProductRepository->getListByGroupId($campaignProduct->group_id);
        } else {
            $campaignProducts = collect([$campaignProduct]);
        }

        return $campaignProducts;
    }
}
