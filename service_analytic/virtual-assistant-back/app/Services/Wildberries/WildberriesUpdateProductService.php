<?php

namespace App\Services\Wildberries;

use App\Classes\Helper;
use App\Helpers\WbProductHelper;
use App\Jobs\DashboardAccountUpdateJob;
use App\Jobs\UpdateProductInWildberries;
use App\Jobs\UpdateWbOptimisation;
use App\Models\WbNomenclature;
use App\Models\WbPickList;
use App\Models\WbProduct;
use App\Models\WbUsingKeyword;
use App\Repositories\Interfaces\Wildberries\WildberriesProductRepositoryInterface;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class WildberriesUpdateProductService implements WildberriesProductRepositoryInterface
{
    private $jobsUpdateProducts = [];

    /**
     * Update WB product
     */
    public function updateProduct
    (
        $id,
        $accountId,
        $newData,
        $dataNomenclatures,
        $nomenclatures,
        $nomenclaturesFromData,
        $productForUpdate,
        $isMassUpdate = false
    ): array
    {
        $product = WbProduct::where('id', $id)->where('account_id', $accountId)->first();

        if (!$product) {
            return ['status' => 'error', 'message' => "Product $id is not found for account $accountId"];
        }

        $oldData = Helper::getUsableData($product);

        $this->getNewNomenclatures($newData, $oldData, $dataNomenclatures);
        $this->setNewAddins($newData, $oldData);
        $this->saveAddins($product, $oldData);

        // Сохранение ценовых данных в номенклатурах продукта
        $status = $this->updateAttachedPriceNomenclatures($nomenclatures, $nomenclaturesFromData);
        if ($status['status'] === 'error') {
            return $status;
        }

        // Актуализируем список в таблице wb_using_keywords
        $this->updateWildberriesKeywords($id);

        if (app()->runningInConsole() === false && $isMassUpdate === false) {
            DashboardAccountUpdateJob::dispatch(
                UserService::getUserId(),
                UserService::getAccountId(),
                UserService::getCurrentAccountPlatformId()
            )->delay(now()->addMinute(2));
        }

        return $this->saveProduct($productForUpdate, $product, $isMassUpdate);
    }

    public function massUpdate($products, $accountId, $productForUpdate, $userId)
    {
        $savedProducts = [];
        $idsForBlock = [];
        $productNumber = 0;
        foreach ($products as $product) {
            $idsForBlock[] = $product['id'];
            $savedProducts[] = $this->updateProduct(
                $product['id'],
                $accountId,
                $product['data'],
                $product['data_nomenclatures'],
                $product['nomenclatures'],
                $product['data']['nomenclatures'],
                $productForUpdate[$productNumber],
                true
            );
            $productNumber++;
        }

        $email = request()->input('user')['email'];
        WbProduct::whereIn('id', $idsForBlock)->update(['is_block' => 1]);

        if (app()->runningInConsole() === false) {
            DashboardAccountUpdateJob::dispatch(
                UserService::getUserId(),
                UserService::getAccountId(),
                UserService::getCurrentAccountPlatformId()
            )->delay(now()->addMinute(5));
        }

        Bus::batch($this->jobsUpdateProducts)
            ->finally(function (Batch $batch) use ($userId, $email, $idsForBlock) {
                WbProduct::whereIn('id', $idsForBlock)->update(['is_block' => 0]);
                UsersNotification::dispatch(
                    'marketplace.account_product_update_success',
                    [['id' => $userId, 'lang' => 'ru', 'email' => $email]],
                    ['count' => $batch->totalJobs - $batch->failedJobs, 'total' => $batch->totalJobs, 'marketplace' => 'Wildberries']
                );
            })
            ->onQueue('default_long')
            ->name('Update wb products batch')
            ->dispatch();
        return ['status' => 'success', 'message' => 'Товары в процессе обновления', 'data' => $savedProducts];
    }

    /**
     * Get new nomenclatures
     * @param $newData
     * @param $oldData
     * @param $request
     * @return void
     */
    private function getNewNomenclatures(&$newData, &$oldData, $data_nomenclatures)
    {
        if (isset($newData['addin']) && $newData['addin']) {
            foreach ($newData['addin'] as $i => $a) {
                if (isset($a['params'])) {
                    foreach ($a['params'] as $k => $v) {
                        if (isset($v['value']['value'])) {
                            $newData['addin'][$i]['params'][$k]['value'] = $v['value']['value'];
                        }
                        if (isset($v['value']->value)) {
                            $newData['addin'][$i]['params'][$k]['value'] = $v['value']->value;
                        }
                    }
                }
            }
            $oldData->addin = $newData['addin'] ?? null;
        }
        $newData['nomenclatures'] = $data_nomenclatures;
    }

    /**
     * @param $newData
     * @param $oldData
     * @return void
     */
    private function setNewAddins(&$newData, &$oldData)
    {
        foreach ($newData['nomenclatures'] as $i => $n) {
            if (!isset($n['addin'])) {
                continue;
            }
            // Новый addins
            $newAddin = [];
            foreach ($n['addin'] as $a) {
                // Удаляем пустые параметры в номенклатурах
                if (($a['params'] !== []) && ($a['params'] != [0 => ["value" => null]]) && $a['params']) {
                    foreach ($a['params'] as $k => $v) {
                        if (isset($v['value'])) {
                            $a['params'][$k]['value'] = $v['value'];
                        }
                        if (isset($v->value)) {
                            $a['params'][$k]['value'] = $v->value;
                        }
                    }
                    $newAddin[] = $a;
                }
            }
            $newData['nomenclatures'][$i]['addin'] = $newAddin;
            $oldData->nomenclatures[$i]->addin = $newAddin;
        }

        $oldData->data_nomenclatures = $newData['nomenclatures'] ?? null;
    }

    /**
     * @param $product
     * @param $oldData
     * @return void
     */
    private function saveAddins($product, $oldData)
    {
        $product->data = WbProductHelper::clearCardAddins($oldData);
        $product->save();
    }

    /**
     * Update nomenclatures
     * @param $request
     * @return array|string[]
     */
    private function updateAttachedPriceNomenclatures($nomenclatures, $dataNomenclatures): array
    {
        // Сохранение ценовых данных в номенклатурах продукта
        $attachedNomenclatures = $nomenclatures;
        if (isset($dataNomenclatures)) {
            foreach ($dataNomenclatures as $k => $nm) {
                $attachedNomenclature = $attachedNomenclatures[$k] ?? null;
                if (!$attachedNomenclature) {
                    return ['status' => 'error', 'message' => 'Nomenlatures not found!', 'code' => 403];
                }
                WbNomenclature::where('nm_id', $nm['nmId'])
                    ->update([
                        'price' => $attachedNomenclature['price'],
                        'discount' => $attachedNomenclature['discount'],
                        'promocode' => $attachedNomenclature['promocode'] ?? '',
                    ]);
            }
        }
        return ['status' => 'success'];
    }

    /**
     * Update keywords for WB product
     * @param $id
     * @return void
     */
    private function updateWildberriesKeywords($id): void
    {
        $wbPickList = WbPickList::select('wb_product_id', 'name', 'popularity')
            ->where('wb_product_id', $id)
            ->get();

        $wbUsingKeywordToSave = $wbPickList->toArray();
        $wbUsingKeywordToSave = array_map(function ($element) {
            $element['created_at'] = now();
            return $element;
        }, $wbUsingKeywordToSave);

        WbUsingKeyword::where('wb_product_id', $id)->delete();
        WbUsingKeyword::insert($wbUsingKeywordToSave);
        WbPickList::where('wb_product_id', $id)->delete();
    }

    /**
     * @param $request
     * @param $product
     * @return array
     */
    private function saveProduct($productForUpdate, $product, $isMassUpdate = false): array
    {
        if ($product) {
            $product->update($productForUpdate);
            $product->save();
            dispatch(new UpdateWbOptimisation($product));
            $this->sync($product->id, $product->account_id, $isMassUpdate);
            return ['status' => 'success', 'data' => $product, 'code' => 200];
        } else {
            return ['status' => 'error', 'message' => 'product not found', 'code' => 404];
        }
    }

    public function sync($id, $accountId, $isMassUpdate = false): bool
    {
        $product = WbProduct::where('id', $id)->where('account_id', $accountId)->first();
        if ($product) {
            // Send product to sync
            if ($isMassUpdate) {
                $this->jobsUpdateProducts[] = new UpdateProductInWildberries($product, request()->input('user'), false);
            } else {
                UpdateProductInWildberries::dispatch($product, request()->input('user'));
            }
            return true;
        }
        return false;
    }

    /**
     * @param $request
     * @return array
     */
    public function getFieldsForUpdateMass($request): array
    {
        $productsArray = [];
        $only = ['supplier_id', 'imt_supplier_id', 'title', 'brand', 'barcodes', 'nmid', 'sku', 'image', 'price',
            'object', 'parent', 'country_production', 'nomenclatures', 'data_nomenclatures',
            'recommendations', 'recommended_characteristics', 'required_characteristics', 'dimension_unit',
            'depth', 'height', 'width', 'weight_unit', 'weight', 'count_reviews', 'is_test', 'is_notificated', 'status',
            'status_id', 'rating', 'price_range', 'url', 'optimization', 'key_requests', 'status_id'];

        $counter = 0;
        foreach ($request->products as $product) {
            $productsArray[$counter]['user_id'] = $request->user_id;
            $productsArray[$counter]['account_id'] = $request->account_id;
            foreach ($only as $field) {
                if (isset($product->{$field})) {
                    $productsArray[$counter][$field] = $product->{$field};
                }
            }
            if (empty($productsArray[$counter]['barcodes'])) {
                $productsArray[$counter]['barcodes'] = [Helper::genEAN13()];
            }
            $productsArray[$counter]['status_id'] = WbProduct::STATUS_EDITED_LOCAL;
            $productsArray[$counter]['status'] = WbProduct::STATUS_EDITED_LOCAL_TEXT;
            $counter++;
        }
        return $productsArray;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getFieldsForUpdateSingle($request): mixed
    {
        $request->request->add(['status_id' => 5, 'status' => 'Отредактирован локально']);
        return $request->only(
            'supplier_id', 'imt_supplier_id', 'title', 'brand', 'barcodes', 'nmid', 'sku', 'image', 'price',
            'object', 'parent', 'country_production', 'nomenclatures', 'data_nomenclatures',
            'recommendations', 'recommended_characteristics', 'required_characteristics', 'dimension_unit',
            'depth', 'height', 'width', 'weight_unit', 'weight', 'count_reviews', 'is_test', 'is_notificated', 'status',
            'status_id', 'rating', 'price_range', 'url', 'optimization', 'key_requests', 'status_id');
    }
}
