<?php

namespace App\Helpers;

use App\Models\Campaign;
use App\Models\CampaignKeyword;
use App\Models\Group;
use App\Models\Status;
use App\Repositories\Frontend\Campaign\CampaignRepository;
use App\Repositories\V2\Campaign\CampaignProductRepository;
use App\Services\OzonPerformanceService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class OzonHelper
{
    const BUDGET_COEFFICIENT = 1000000;

    protected $connection = null;
    protected array $errors = [];
    protected array $responses = [];
    /** @var CampaignProductRepository */
    protected $campaignProductRepositroy;
    /** @var CampaignRepository */
    protected $campaignRepositroy;

    /**
     * OzonHelper constructor.
     *
     * @return void|JsonResponse
     */
    public function __construct()
    {
        $account = UserService::getCurrentAccount();
        $this->connection = OzonPerformanceService::connect($account);
        $this->campaignProductRepository = new CampaignProductRepository();
        $this->campaignRepository = new CampaignRepository();
    }

    /**
     * Сформировать запрос в Озон на обновление ставок
     *
     * @param integer[] $campaignKeywordIds
     * @param double $newBid
     * @param integer $newStatusId
     *
     * @return bool
     */
    public function makeOzonBidUpdateRequest($campaignKeywordIds, $newBid = null, $newStatusId = null)
    {
        if (!is_array($campaignKeywordIds)) {
            $campaignKeywordIds = (array)$campaignKeywordIds;
        }

        $campaignKeywords = CampaignKeyword::query()
            ->whereIn('id', $campaignKeywordIds)
            ->with(
                'campaignProduct.product:id,sku',
                'campaignProduct.campaignKeywords',
                'campaignProduct.campaignKeywords.keyword:id,name',
                'campaignProduct.campaignStopWords',
                'campaignProduct.campaignStopWords.stopWord:id,name'
            )
            ->get();

        $bids = [];
        foreach ($campaignKeywords as $campaignKeyword) {
            $campaignId = $campaignKeyword->campaignProduct->campaign->ozon_id;

            // Если у компании нет ozon_id - это компания которая создана через интерфейс УС
            // и еще не была отправлена в OZON. В таком случае передавать данные об обновлении в OZON не нужно
            // останавливаем выполнение сценария и возвращаем метку об успешном выполнении
            if (!$campaignId) {
                return true;
            }

            $sku = $campaignKeyword->campaignProduct->product->sku;

            if (!isset($bids[$campaignId][$sku])) {
                $bids[$campaignId][$sku] = [
                    'sku' => (int)$sku,
                    'stopWords' => [],
                    'phrases' => []
                ];

                foreach ($campaignKeyword->campaignProduct->campaignKeywords as $productKeyword) {
                    $oldWords = array_column($bids[$campaignId][$sku]['phrases'], 'phrase');

                    if (!in_array($productKeyword->keyword->name,
                            $oldWords) && $productKeyword->status_id !== Status::ARCHIVED && $productKeyword->status_id !== Status::DELETED) {
                        $bids[$campaignId][$sku]['phrases'][$productKeyword->id] = [
                            'phrase' => $productKeyword->keyword->name,
                            'bid' => $productKeyword->status_id == Status::ACTIVE ? (int)self::BUDGET_COEFFICIENT * $productKeyword->bid : 0
                        ];
                    }
                }

                foreach ($campaignKeyword->campaignProduct->campaignStopWords as $campaignStopWord) {
                    if (!in_array($campaignStopWord->stopWord->name,
                            $bids[$campaignId][$sku]['stopWords']) && $campaignStopWord->status_id !== Status::ARCHIVED && $campaignStopWord->status_id !== Status::DELETED) {
                        $bids[$campaignId][$sku]['stopWords'][] = $campaignStopWord->stopWord->name;
                    }
                }
            }

            // Определяем действие: обновление ставки или обновление статуса
            if (isset($newStatusId)) {
                $newBid = $newStatusId == Status::ACTIVE ? $campaignKeyword->bid : 0;
            } elseif (isset($newBid) && $campaignKeyword->status_id == Status::NOT_ACTIVE) {
                $newBid = 0;
            }

            $bids[$campaignId][$sku]['phrases'][$campaignKeyword->id] = [
                'phrase' => $campaignKeyword->keyword->name,
                'bid' => (int)self::BUDGET_COEFFICIENT * $newBid
            ];
        }

        $result = true;
        foreach ($bids as $campaignId => &$campaignBids) {
            $campaignBids = array_values($campaignBids);
            foreach ($campaignBids as &$skuBids) {
                $skuBids['phrases'] = array_values($skuBids['phrases']);
            }

            $res = $this->connection->updateProductsBids($campaignId, ['bids' => $campaignBids]);
            if (!$res) {
                $this->errors[] = $this->connection::getLastError();
                $result = false;
            }
            $this->responses[$campaignId] = $res;
        }

        return $result;
    }

    /**
     * Сформировать запрос в Озон на обновление ставок группы
     *
     * @param integer[] $campaignKeywordIds
     * @param integer $groupId
     * @param double $newBid
     * @param integer $newStatusId
     *
     * @return bool
     */
    public function makeOzonGroupBidUpdateRequest($campaignKeywordIds, $groupId, $newBid = null, $newStatusId = null)
    {
        $campaignKeywords = CampaignKeyword::query()
            ->whereIn('id', $campaignKeywordIds)
            ->where('group_id', $groupId)
            ->with(
                'campaignProduct.product:id,sku',
                'campaignProduct.campaignKeywords',
                'campaignProduct.campaignKeywords.keyword:id,name',
                'campaignProduct.campaignStopWords',
                'campaignProduct.campaignStopWords.stopWord:id,name'
            )
            ->get();

        $group = Group::find($groupId);
        $campaignId = $group->campaign->ozon_id;

        // Если у компании нет ozon_id - это компания которая создана через интерфейс УС
        // и еще не была отправлена в OZON. В таком случае передавать данные об обновлении в OZON не нужно
        // останавливаем выполнение сценария и возвращаем метку об успешном выполнении
        if (!$campaignId) {
            return true;
        }

        $bids = [
            'title' => $group->name,
            'stopWords' => [],
            'phrases' => []
        ];

        foreach ($campaignKeywords as $campaignKeyword) {
            foreach ($campaignKeyword->campaignProduct->campaignKeywords as $productKeyword) {
                if (count($bids['phrases']) < 100) {
                    $bids['phrases'][$productKeyword->id] = [
                        'phrase' => $productKeyword->keyword->name,
                        'bid' => $productKeyword->status_id == Status::ACTIVE ? (int)self::BUDGET_COEFFICIENT * $productKeyword->bid : 0
                    ];
                }
            }

            foreach ($campaignKeyword->campaignProduct->campaignStopWords as $campaignStopWord) {
                $bids['stopWords'][] = $campaignStopWord->stopWord->name;
            }

            // Определяем действие: обновление ставки или обновление статуса
            if (isset($newStatusId)) {
                $newBid = $newStatusId == Status::ACTIVE ? $campaignKeyword->bid : 0;
            } elseif (isset($newBid) && $campaignKeyword->status_id == Status::NOT_ACTIVE) {
                $newBid = 0;
            }

            $bids['phrases'][$campaignKeyword->id] = [
                'phrase' => $campaignKeyword->keyword->name,
                'bid' => (int)self::BUDGET_COEFFICIENT * $newBid
            ];
        }

        $bids['phrases'] = array_values($bids['phrases']);

        $res = $this->connection->updateGroupBids($campaignId, $group->ozon_id, $bids);
        if (!$res) {
            $this->errors[] = $this->connection::getLastError();
        }
        $this->responses[$groupId] = $res;

        return $res;
    }

    /**
     * @param Campaign $campaign
     * @return false|mixed
     */
    public function ozonUpdateWords(Campaign $campaign)
    {
        $ozonKeywordData['bids'] = [];
        $ozonKeywordGroupData = [];
        $campaignProducts = $this->campaignProductRepository->getListByCampaignId($campaign->id);

        foreach ($campaignProducts as $campaignProduct) {
            $ozonProductKeywords = [];
            $ozonProductStopWords = [];

            foreach ($campaignProduct->campaignKeywords as $productKeyword) {
                $oldWords = array_column($ozonProductKeywords, 'phrase');

                if (!in_array($productKeyword->keyword->name,
                        $oldWords) && $productKeyword->status_id !== Status::ARCHIVED && $productKeyword->status_id !== Status::DELETED) {
                    $ozonProductKeywords[] = [
                        'phrase' => $productKeyword->keyword->name,
                        'bid' => $productKeyword->status_id == Status::ACTIVE ? $productKeyword->bid * (int)self::BUDGET_COEFFICIENT : 0,
                    ];
                }
            }

            foreach ($campaignProduct->campaignStopWords as $productStopWord) {
                if (!in_array($productStopWord->stopWord->name,
                        $ozonProductStopWords) && $productStopWord->status_id !== Status::ARCHIVED && $productStopWord->status_id !== Status::DELETED) {
                    $ozonProductStopWords[] = $productStopWord->stopWord->name;
                }
            }

            if ($campaignProduct->group_id) {
                $groupOzonId = $campaignProduct->group->ozon_id;

                if (!isset($ozonKeywordGroupData[$groupOzonId])) {
                    $ozonKeywordGroupData[$groupOzonId] = [
                        'title' => $campaignProduct->group->name,
                        'stopWords' => $ozonProductStopWords,
                        'phrases' => $ozonProductKeywords
                    ];
                }
            } else {
                $ozonKeywordData['bids'][] = [
                    'sku' => $campaignProduct->product->sku,
                    'stopWords' => $ozonProductStopWords,
                    'phrases' => $ozonProductKeywords
                ];
            }
        }
        foreach ($ozonKeywordGroupData as $key => $group) {
            $res = $this->connection->updateGroupBids($campaign->ozon_id, $key, $group);

            if (!$res) {
                return false;
            }
        }
        return $this->connection->updateWordsToCampaignProducts($campaign->ozon_id, $ozonKeywordData);
    }

    /**
     * @param Campaign $campaign
     * @return \Illuminate\Http\JsonResponse
     */
    public function ozonCreateCampaign(Campaign $campaign)
    {
        $campaignCreateParams = [
            'title' => $campaign->name,
            'fromDate' => $campaign->start_date,
            'toDate' => $campaign->end_date,
            'dailyBudget' => $campaign->budget * self::BUDGET_COEFFICIENT,
            'placement' => $campaign->placement->code
        ];

        return $this->connection->createCampaign($campaignCreateParams);
    }

    /**
     * @param int $campaignId
     * @return mixed
     */
    public function ozonUpdateCampaign(int $campaignId)
    {
        $campaign = $this->campaignRepository->getItem($campaignId);

        if ($campaign->ozon_id) {
            $requestParams = [
                'dailyBudget' => $campaign->budget * self::BUDGET_COEFFICIENT,
                'fromDate' => $campaign->start_date,
                'toDate' => $campaign->end_date,
                'expenseStrategy' => 'DAILY_BUDGET'
            ];
            return $this->connection->updateCampaign($campaign->ozon_id, $requestParams);
        }
        return false;
    }

    /**
     * @param int $campaignId
     * @return false|\Illuminate\Http\JsonResponse|mixed
     */
    public function ozonActivateCampaign(int $campaignId)
    {
        return $this->connection->activateCampaign($campaignId);
    }

    /**
     * @param int $campaignId
     * @return false|\Illuminate\Http\JsonResponse|mixed
     */
    public function ozonDeactivateCampaign(int $campaignId)
    {
        return $this->connection->deactivateCampaign($campaignId);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getResponses()
    {
        return $this->responses;
    }
}
