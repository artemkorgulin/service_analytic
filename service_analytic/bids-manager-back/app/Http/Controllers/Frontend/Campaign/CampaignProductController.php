<?php

namespace App\Http\Controllers\Frontend\Campaign;

use App\Http\Requests\V1\Campaign\CampaignProductsIdsStoreMultipleRequest;
use App\Models\Campaign;
use App\Models\Group;
use App\Http\Requests\V1\Campaign\CampaignProductUpdateMultipleRequest;
use App\Repositories\Frontend\Campaign\CampaignRepository;
use App\Repositories\V2\Campaign\CampaignProductRepository;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use App\Models\CampaignProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Campaign\CampaignProductStoreMultipleRequest;
use App\Http\Requests\V1\Campaign\CampaignProductSetStatusRequest;
use App\Http\Requests\V2\Campaign\CampaignSaveRequest;
use App\Http\Requests\V1\Campaign\CampaignProductIndexRequest;
use App\Repositories\V2\Product\ProductRepository;
use App\Repositories\Frontend\Product\GroupRepository;
use App\Models\Status;

class CampaignProductController extends Controller
{
    /**
     * @param CampaignProductStoreMultipleRequest $request
     * @return JsonResponse
     */
    public function storeMultiple(CampaignProductStoreMultipleRequest $request, ProductRepository $productRepository)
    {
        /** @var array $withoutGroupSku */
        $withoutGroupSku = array(
            'group_name' => null,
            'products_sku' => $request->input('without_group') ?? array()
        );
        $productGroups = $request->get('with_groups') ?? array();
        $productGroups[] = $withoutGroupSku;
        $missedSku = [];
        foreach ($productGroups as $productsGroup) {
            if (isset($productsGroup['name'])) {
                $group = new Group();
                $group->name ??= $productsGroup['name'];
                $result = $group->save();
            }
            foreach ($productsGroup['products_sku'] as $productSku) {
                $productId = $productRepository->getProduct($productSku, UserService::getUserId());
                if ($productId) {
                    $campaignProduct = new CampaignProduct();
                    $campaignProduct->campaign_id = $request->input('campaign_id');
                    $campaignProduct->product_id = $productId;
                    $campaignProduct->status_id = Status::ACTIVE;
                    $campaignProduct->group_id = isset($result) ? $group->id : null;
                    $result = $campaignProduct->save();
                    if (!$result) {
                        $missedSku[] = $productSku;
                    }
                } else {
                    $missedSku[] = $productSku;
                }
            }

        }

        return response()->api_success(['missedSku' => $missedSku]);
    }

    /**
     * Сохранение товаров, групп. Удаление товаров из групп
     *
     * @param CampaignProductStoreMultipleRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function storeMultipleIds(CampaignProductsIdsStoreMultipleRequest $request)
    {
        $campaign_id = $request->input('campaign_id');
        $deletedProducts = $request->input('deleted_products') ?? [];
        $withoutGroupIds = $request->input('without_group.products') ?? [];
        $productsGroups = $request->input('with_group.products') ?? [];
        $groups = $request->input('with_group.group') ?? null;;
        $result = [];

        // Create group and add  products
        if (isset($groups['name'])) {
            $group = new Group();
            $group->name ??= $groups['name'];
            $group->campaign_id = $campaign_id;
            //$resultGroup = $group->save(); //
            $resultGroup = $group->updateOrCreate(['campaign_id' => $group->campaign_id, 'name' => $group->name], ['name' => $group->name]);
            $result[] = $this->addProducts($campaign_id, $productsGroups, $resultGroup->id ?? null);
        }

        // Add products without group
        $result[] = $this->addProducts($campaign_id, $withoutGroupIds, null);
        // Delete products
        $result[] = $this->removeProducts($campaign_id, $deletedProducts);

        return response()->api_success($result);
    }


    /**
     * @param CampaignProductSetStatusRequest $request
     * @return JsonResponse
     */
    public function updateStatus(CampaignProductSetStatusRequest $request, CampaignProductRepository $campaignProductRepository)
    {
        $campaignProduct = $campaignProductRepository->getItem($request->get('campaign_product_id'));
        $campaignProduct->status_id = $request->get('status_id');
        $campaignProduct->save();

        return response()->api_success([]);
    }


    /**
     * @param CampaignProductUpdateMultipleRequest $request
     * @param integer $campaignId
     *
     * @return JsonResponse
     */
    public function updateMultiple(
        CampaignProductUpdateMultipleRequest $request,
                                             $campaignId,
        CampaignRepository                   $campaignRepository,
        CampaignProductRepository            $campaignProductRepository,
        ProductRepository                    $productRepository,
        GroupRepository                      $groupRepository
    )
    {
        $errors = [];

        /** @var array $withoutGroup */
        $withoutGroup = $request->input('without_group') ?? [];
        $withoutGroup['group'] = [
            'id' => null,
            'name' => null,
        ];
        $productsGroups = $request->get('with_group') ?? [];
        $movedProducts = $request->get('moved_products') ?? [];
        $productsGroups[] = $withoutGroup;

        $missedGroups = [];
        $missedSku = [];

        /** @var Campaign $campaign */
        $campaign = $campaignRepository->getItem($campaignId);
        $account = UserService::getCurrentAccount();
//        $ozonConnection = OzonPerfomanceService::connect($account);
//
//        if (!$ozonConnection) {
//            return response()->json(
//                [
//                    'success' => false,
//                    'data'    => [],
//                    'errors'  => [__('front.ozon_error')],
//                ]
//            );
//        }

        $savedProducts = [];
        $savedGroups = [];
        $withGroup['bids'] = [];
        foreach ($productsGroups as $productsGroup) {
            $groupId = null;

            if (isset($productsGroup['products']) && count($productsGroup['products'])) {
                if (!($productsGroup['group']['id'])) {
                    if ($productsGroup['group']['name']) {
                        $group = Group::query()
                            ->where([
                                ['name', $productsGroup['group']['name']],
                                ['campaign_id', $campaignId],
                            ])
                            ->first();

                        if (!$group) {
                            $group = new Group();
                            $group->name = $productsGroup['group']['name'];
                            $group->campaign_id = $campaignId;
                            $groupResult = $group->save();
                            if (!$groupResult) {
                                $missedGroups[] = $productsGroup['group']['name'];
                                continue;
                            }

                            $groupId = $group->id;
                        } else if ($group->status_id !== Status::ACTIVE) {
                            $group->status_id = Status::ACTIVE;
                            $groupId = $group->id;
                            $group->save();
                        } else {
                            return response()->json([
                                'success' => false,
                                'data' => [],
                                'errors' => [__('updater.unique_group_name', ['groupName' => $productsGroup['group']['name']])],
                            ], 422);
                        }
                    }
                } else {
                    $groupId = $productsGroup['group']['id'];
                }

                if (isset($groupId)) {
                    $savedGroups[] = $groupId;
                }
                if (isset($productsGroup['products']) && count($productsGroup['products'])) {
                    foreach ($productsGroup['products'] as $product) {
                        $needKeywordUpdate = false;
                        if (isset($product['id'])) {
                            $campaignProduct = $campaignProductRepository->getItem($product['id']);
                            if ($campaignProduct->product->sku != $product['sku']) {
                                $missedSku[] = $product['sku'];
                                continue;
                            }
                            if ($campaignProduct->group_id != $groupId) {
                                $needKeywordUpdate = true;
                            }
                            $campaignProduct->group_id = $groupId;
                            $result = $campaignProduct->save();
                            if (!$result) {
                                $missedSku[] = $product['sku'];
                            } else {
                                $savedProducts[] = $campaignProduct->id;
                            }
                        } else {
                            $needKeywordUpdate = $groupId ? true : false;
                            $productId = $productRepository->getProduct($product['sku'], UserService::getUserId());
                            if ($productId) {
                                $campaignProduct = new CampaignProduct();
                                $campaignProduct->campaign_id = $campaignId;
                                $campaignProduct->product_id = $productId;
                                $campaignProduct->status_id = Status::ACTIVE;
                                $campaignProduct->group_id = $groupId;
                                $result = $campaignProduct->save();
                                if (!$result) {
                                    $missedSku[] = $product['sku'];
                                } else {
                                    $savedProducts[] = $campaignProduct->id;
                                }
                            } else {
                                $missedSku[] = $product['sku'];
                            }
                        }
                        if ($needKeywordUpdate) {
                            // $this->updateCampaignProductWords($campaignProduct);
                        }
                        if ($groupId) {
                            $group = $groupRepository->getItem($groupId);
                            if (!$group->ozon_id && $campaign->ozon_id) {
                                //                            $ozonGroupResult = $ozonConnection->addGroupToCampaign($campaign->ozon_id, ['title' => $group->name]);
                                //                            $group->ozon_id = $ozonGroupResult ? $ozonGroupResult->groupId : null;
                                //                            $group->save();
                            }
                            $withGroup['bids'][] = [
                                'sku' => $campaignProduct->product->sku,
                                'group_id' => $group->ozon_id,
                            ];
                        }
                    }
                }
            }
        }

        $this->removeOldProducts($campaignId, $savedProducts);
        $this->removeOldGroups($campaignId, $savedGroups);

        if ($campaign->ozon_id) {
            $ozonRemoveResult = true;

//            if (count($resultRemove)) {
//                $ozonRemoveResult = $ozonConnection->removeProductFromCampaign($campaign->ozon_id, [
//                    'sku' => $resultRemove
//                ]);
//            }

//            $ozonGrouplessProducts['bids'] = [];
//            foreach ($withoutGroup['products'] as $product) {
//                array_push($ozonGrouplessProducts['bids'], [
//                    'sku' => $product['sku'],
//                ]);
//            }

//            $ozonProductsResult = $ozonConnection->addProductsToCampaign($campaign->ozon_id, $ozonGrouplessProducts);
//            $ozonGroupResult = $ozonConnection->addProductsToCampaign($campaign->ozon_id, $withGroup);

//            $success = $ozonProductsResult && $ozonGroupResult && $ozonRemoveResult;

//            if (OzonPerfomanceService::getLastError()) {
//                $errors[] = __('front.ozon_error');
//            }
        }

        return response()->api_success(['missedSku' => $missedSku, 'missedGroups' => $missedGroups]);
    }

    /**
     * @param $campaignId
     * @param $savedProducts
     * @throws \Exception
     */
    public function removeOldProducts($campaignId, $savedProducts, CampaignProductRepository $campaignProductRepository)
    {
        $itemsSku = [];
        $oldProducts = $campaignProductRepository->getListByCampaignId($campaignId);
        /** @var CampaignProduct $oldProduct */

        foreach ($oldProducts as $oldProduct) {
            if (!in_array($oldProduct->id, $savedProducts) && $oldProduct->status_id !== Status::ARCHIVED) {
                $oldProduct->status_id = Status::ARCHIVED;
                $oldProduct->save();

                if ($oldProduct->product->sku) {
                    $itemsSku[] = $oldProduct->product->sku;
                }
            }
        }

        return $itemsSku;
    }

    /**
     * @param $campaignId
     * @param $deketedProducts
     * @throws \Exception
     */
    public function removeProducts($campaignId, $deletedProducts, CampaignProductRepository $campaignProductRepository)
    {
        $itemsId = [];
        $oldProducts = $campaignProductRepository->getListByCampaignId($campaignId);

        foreach ($oldProducts as $oldProduct) {
            if (count($deletedProducts) > 0 && in_array($oldProduct->product_id, $deletedProducts) && $oldProduct->status_id !== Status::ARCHIVED) {
                $oldProduct->status_id = Status::ARCHIVED;
                $oldProduct->save();

                if ($oldProduct->product_id) {
                    $itemsId['deleted'][] = $oldProduct->product_id;
                }
            }
        }

        return $itemsId;
    }

    /**
     * @param $campaignId int
     * @param $doodIds []
     * @param $groupId
     *
     * @throws \Exception
     */
    public function addProducts($campaignId, $productIds, $groupId = null, ProductRepository $productRepository)
    {
        $itemsId = [];
        foreach ($productIds as $productId) {
            $product = $productRepository->getProduct($productId, UserService::getUserId());
            if ($product) {
                $campaignProduct = new CampaignProduct();
                $campaignProduct->campaign_id = $campaignId;
                $campaignProduct->product_id = $product->id;
                $campaignProduct->status_id = Status::ACTIVE;
                $campaignProduct->group_id = $groupId;
                $result = $campaignProduct->updateOrCreate(
                    ['campaign_id' => $campaignProduct->campaign_id, 'product_id' => $product->id],
                    ['status_id' => $campaignProduct->status_id, 'group_id' => $campaignProduct->group_id]
                );
                if (!$result) {
                    $itemsId['missed'][][] = $productId;
                }
            } else {
                $itemsId['add'][][] = $productId;
            }
        }

        return $itemsId;
    }

    /**
     * @param $campaignId
     * @param $savedGroups
     * @throws \Exception
     */
    public function removeOldGroups($campaignId, $savedGroups)
    {
        $oldGroups = Group::where('campaign_id', $campaignId)->get();

        foreach ($oldGroups as $oldGroup){
            if(!in_array($oldGroup->id, $savedGroups)){
                $oldGroup->status_id = Status::DELETED;
                $oldGroup->save();
            }
        }
    }


    /**
     * @param CampaignProductIndexRequest $request
     * @return JsonResponse
     */
    public function index(CampaignProductIndexRequest $request, CampaignProductRepository $campaignProductRepository)
    {
        $campaignProductList = $campaignProductRepository->getListByCampaignId($request->get('campaign_id'));
        $groups = Group::where('campaign_id', '=', $request->get('campaign_id'))->get() ?? [];
        $groupProducts = [];

        foreach ($groups as $group) {
            //$group_products[$group->id] =  $this->campaignProductRepository->getCampaignGroupProducts($request->get('campaign_id'), $group->id);
            $groupProducts[$group->id] = $campaignProductRepository->getListByGroupId($group->id);
        }

        return response()->api_success([
            'campaign_product_list' => $campaignProductList,
            'groups' => Group::where('campaign_id', '=', $request->get('campaign_id'))->get(),
            'group_products' => $groupProducts,
            'campaign' => Campaign::find($request->get('campaign_id')),
        ]);
    }

    /**
     * @param CampaignSaveRequest $request
     */
    public function store(CampaignSaveRequest $request)
    {
        $campaignProduct = new CampaignProduct($request->input());
        $campaignProduct->save();

        return response()->api_success([]);
    }
}
