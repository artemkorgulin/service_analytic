<?php

namespace App\Services\Escrow;

use App\Constants\Errors\EscrowErrors;
use App\Exceptions\Escrow\EscrowException;
use App\Http\Requests\EscrowAbuseRequest;
use App\Http\Requests\EscrowOzonRequest;
use App\Http\Requests\EscrowWildberriesRequest;
use App\Models\OzProduct;
use App\Models\WbProduct;
use App\Services\Escrow\Interfaces\EscrowServiceInterface;
use App\Services\UserService;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Http\JsonResponse;
use League\Csv\Exception;

class EscrowService extends EscrowMethods implements EscrowServiceInterface
{
    /**
     * Get escrow remain and total limits
     *
     * @return JsonResponse
     */
    public function getEscrowLimits(): JsonResponse
    {
        return response()->api_success([
            'remainLimit' => $this->getRemainEscrowLimit(),
            'totalLimit' => $this->getEscrowLimit(),
        ]);
    }


    /**
     * Get escrow remain and limits product
     * @param $product_id
     * @return array
     */
    public function getEscrowLimitsProduct($product_id): array
    {
        return [
            'imagesProductProtected' => $this->getRemainEscrowLimitProduct($product_id),
            'imagesProductTotal' => $this->getEscrowLimitProduct($product_id),
        ];
    }

    /**
     * Get escrow devide and limits product
     * @param $product_id
     * @return array
     */
    public function getEscrowDevideToProduct($product_id): array
    {
        $is_protect = ($this->getDevideRemainEscrowProtectedProduct($product_id)/$this->getEscrowLimitProduct($product_id));

        return [
            'is_protect' => $is_protect,
        ];
    }

    /**
     * Check OZON images and save to table hashes
     * @param EscrowOzonRequest $escrowRequest
     * @param IregService $iregService
     * @param $productId
     * @return array|null
     */
    public function processOzonEscrow(EscrowOzonRequest $escrowRequest, IregService $iregService, $productId): ?array
    {
        $product = OzProduct::findWithCurrentUser($productId);
        if (!$product) {
            return ['error' => "Товар не найден"];
        }
        return $this->processEscrowForOzonProduct($escrowRequest, $iregService, $product);
    }

    /**
     * Check WB images and save to table hashes
     * @param EscrowWildberriesRequest $escrowRequest
     * @param IregService $iregService
     * @param EscrowWildberriesRequest $escrowWildberriesRequest
     * @return array|null
     */
    public function processWbEscrow
    (
        EscrowWildberriesRequest $escrowRequest,
        IregService $iregService,
        EscrowWildberriesRequest $escrowWildberriesRequest
    ): ?array
    {
        $product = WbProduct::findWithCurrentUser($escrowWildberriesRequest->product_id);
        if (!$product) {
            return ['error' => "Товар не найден"];
        }
        return $this->processEscrowForWildberriesProduct($escrowRequest, $iregService, $product);
    }

    /**
     * @param EscrowOzonRequest $escrowRequest
     * @param IregService $iregService
     * @param $product
     * @return array|null
     */
    private function processEscrowForOzonProduct(EscrowOzonRequest $escrowRequest, IregService $iregService, $product): ?array
    {
        try {
            ModelHelper::transaction(function () use ($escrowRequest, $iregService, $product) {
                $images = $this->getAllOzonImages($product);
                if (empty($images)) {
                    throw new EscrowException(EscrowErrors::NO_IMAGES);
                }
                $hashes = $this->getImageHashes($images);
                if (empty($hashes)) {
                    throw new EscrowException(EscrowErrors::NO_HASHES);
                }
                $saved = $this->saveEscrowResult($product->id, $hashes, $escrowRequest);
                if (empty($saved)) {
                    throw new EscrowException(EscrowErrors::NO_ESCROW);
                }
                $zipPath = $this->makeImagesZip($images, $product->sku);
                try {
                    $certificates = $iregService->fileStore($zipPath, $product, $escrowRequest)->response->certificates;
                } catch (\Exception $exception) {
                    throw new EscrowException(EscrowErrors::NO_CERTIFICATES);
                }
                $this->saveCertificates($certificates, $product, $escrowRequest);
                $this->removeZip($zipPath);
            });
        } catch (\Exception $exception) {
            if (empty(EscrowException::MESSAGES[$exception->getCode()])) {
                report($exception);
            }
            return ['error' => $exception->getMessage()];
        }
        return [
            'hashes' => $product->escrowHash()->get(),
            'certificates' => $product->certificate()->get(),
            'percent' => $this->getEscrowPercentForOzon($product),
            'history' => $this->productEscrowHistory($product)
        ];
    }

    /**
     * @param EscrowWildberriesRequest $escrowRequest
     * @param IregService $iregService
     * @param WbProduct $product
     * @return array|null
     */
    private function processEscrowForWildberriesProduct
    (
        EscrowWildberriesRequest $escrowRequest,
        IregService $iregService,
        WbProduct $product
    ): ?array
    {
        try {
            ModelHelper::transaction(function () use ($escrowRequest, $iregService, $product) {
                $images = $this->getImagesByNmId($product, $escrowRequest->nmid);
                if (empty($images)) {
                    throw new EscrowException(EscrowErrors::NO_IMAGES);
                }
                $hashes = $this->getImageHashes($images);
                if (empty($hashes)) {
                    throw new EscrowException(EscrowErrors::NO_HASHES);
                }
                $saved = $this->saveEscrowResult($product->id, $hashes, $escrowRequest);
                if (empty($saved)) {
                    throw new EscrowException(EscrowErrors::NO_ESCROW);
                }
                $zipPath = $this->makeImagesZip($images, $escrowRequest->nmid);
                try {
                    $certificates = $iregService->fileStore($zipPath, $product, $escrowRequest)?->response?->certificates;
                } catch (\Exception $exception) {
                    throw new EscrowException(EscrowErrors::NO_CERTIFICATES);
                }
                $this->saveCertificates($certificates, $product, $escrowRequest);
                $this->removeZip($zipPath);
            });
        } catch (\Exception $exception) {
            if (empty(EscrowException::MESSAGES[$exception->getCode()])) {
                report($exception);
            }
            return ['error' => $exception->getMessage()];
        }
        return [
            'hashes' => $product->escrowHash()->where('nmid', $escrowRequest->nmid)->get(),
            'certificates' => $product->certificate()->where('nmid', $escrowRequest->nmid)->get(),
            'percent' => $this->getEscrowPercentForNomenclature($product, $escrowRequest->nmid),
            'history' => $this->productEscrowHistory($product, $escrowRequest->nmid)
        ];
    }

    /**
     * Get abuse pdf for product
     *
     * @param EscrowAbuseRequest $escrowAbuseRequest
     * @param EscrowAbuseService $escrowAbuseService
     * @param $model
     * @return mixed
     */
    public function generateAbusePdf(
        EscrowAbuseRequest $escrowAbuseRequest,
        EscrowAbuseService $escrowAbuseService,
        $model
    ): mixed
    {
        $product = $model::findWithCurrentUser($escrowAbuseRequest->product_id);
        if (!$product) {
            return response()->api_fail('Товар не найден!');
        }
        $data = array_merge($escrowAbuseRequest->all(), UserService::getUser());
        $array = optional($product->certificate()->latest()->first())->toArray();
        if (!$array) {
            return response()->api_fail('Не удалось получить сертификат для данного товара!');
        }
        $data = array_merge($data, $array);
        $abuse = $escrowAbuseService->store($data);
        return response()->api_success(['link' => $escrowAbuseService->link($abuse->id)]);
    }

    /**
     * Get escrow data for Wildberries product
     *
     * @param $id
     * @param $nmid
     * @return array
     * @throws \Exception
     */
    public function getEscrowForWbProduct($id, $nmid): array
    {
        $product = WbProduct::currentUserAndAccount()
            ->where('id', $id)->with('nomenclatures')
            ->first();
        if (!$product) {
            return ['status' => 'error', 'message' => 'Product not found!', 'code' => 404];
        }
        $escrow = [
            'remainLimit' => $this->getRemainEscrowLimit(),
            'totalLimit' => $this->getEscrowLimit(),
            'hashes' => $product->escrowHash()->where('nmid', $nmid)->get(),
            'certificates' => $product->certificate()->where('nmid', $nmid)->get(),
            'escrowPercents' => $this->getEscrowPercentForNomenclature($product, $nmid),
            'remainEscrowPercent' => $this->getEscrowLastPercent(),
            'history' => $this->productEscrowHistory($product, $nmid)
        ];
        return ['status' => 'success', 'data' => $escrow];
    }

    /**
     * Get product escrow history
     *
     * @param $product
     * @param int $nmid
     * @param int $limit (default = 30)
     * @return mixed
     */
    public function productEscrowHistory($product, int $nmid = 0, int $limit = 30): mixed
    {
        $history = $product->history();
        if (!empty($nmid)) {
            $history = $history->where('nmid', $nmid);
        }
        $history = $history->orderByDesc('updated_at')->limit($limit)->get();
        return $history ?? null;
    }
}
