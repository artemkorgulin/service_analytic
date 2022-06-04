<?php

namespace App\Services;

use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Helpers\Action\ActionHelper;
use App\Models\VaCategoriesFilter;
use App\Repositories\V1\Assistant\EscrowHashRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\ActionParams;
use Illuminate\Support\Facades\Log;
use stdClass;

class CallsForActionService extends ActionHelper
{
    private string $beforeYesterday;
    private string $yesterday;

    public function __construct()
    {
        $this->beforeYesterday = Carbon::yesterday()->subDay()->format('Y-m-d');
        $this->yesterday = Carbon::yesterday()->format('Y-m-d');
    }

    /**
     * @param HistoryRepositoryInterface $historyRepository
     *
     * @return array
     */
    public function getData(
        HistoryRepositoryInterface $historyRepository,
        ?Collection $products = null
    ): array {
        $timeStart = now();
        $productsPeriodDays = $historyRepository->getProductList();
        $productsYesterday = $productsPeriodDays->where('date', '=', $this->yesterday);
        $productsBeforeYesterday = $productsPeriodDays->where('date', '=', $this->beforeYesterday);

        $vendorCodes = $productsYesterday->pluck('vendor_code');

        $productsTop36PeriodDays = $historyRepository->getProductListTop36($vendorCodes);
        $productsTop36Yesterday = $productsTop36PeriodDays->where('date', '=', $this->yesterday);
        $productsTop36sBeforeYesterday = $productsTop36PeriodDays->where('date', '=', $this->beforeYesterday);

        $result = [];
        $beforeDate = Carbon::yesterday()->subDay()->format('Y-m-d');
        $date = Carbon::yesterday()->format('Y-m-d');

        foreach ($productsYesterday as $product) {
            $productBeforeYesterday = $historyRepository->getProductBeforeDate($productsBeforeYesterday, $product);
            $productTop36Yesterday = $historyRepository->getProductTop36($productsTop36Yesterday, $product);
            $productTop36BeforeYesterday = $historyRepository->getProductTop36BeforeDate($productsTop36sBeforeYesterday,
                $product);
            $result[] = [
                'id' => $product->vendor_code,
                'date' => $date,
                'before_date' => $beforeDate,
                'name' => $this->getName($product),
                'optimization' => $this->getOptimization($product, $productBeforeYesterday),
                'rating' => $this->getRating($product, $productBeforeYesterday,
                    $productTop36Yesterday, $productTop36BeforeYesterday),
                'feedbacks' => $this->getFeedbacks($product, $productBeforeYesterday,
                    $productTop36Yesterday, $productTop36BeforeYesterday),
                'avg_position_category' => $this->getAvgPositionCategory($product,
                    $productBeforeYesterday),
                'avg_position_search' => $this->getAvgPositionSearch($product,
                    $productBeforeYesterday),
                'images_count' => $this->getImagesCount($product, $productBeforeYesterday,
                    $productTop36Yesterday, $productTop36BeforeYesterday),
                'authorship_protection' => $this->getAuthorshipProtection($product,
                    $productBeforeYesterday),
                'sales' => $historyRepository->getDiagramSales($product, $productBeforeYesterday, $productsPeriodDays,
                    $productsTop36PeriodDays,),
            ];
        }

        $timeEnd = now();
        Log::info(__FILE__.'  start= '.$timeStart.' end= '.$timeEnd);

        return $result;
    }

    /**
     * @param int $vendorCode
     *
     * @return mixed
     */
    public function getDiagramData(
        HistoryRepositoryInterface $historyRepository,
        int $vendorCode
    ): mixed {
        $result = [];
        $result['id'] = $vendorCode;

        $products = $historyRepository->getProducts($vendorCode);

        for ($i = ActionParams::PERIOD_MONTH; $i >= 0; $i--) {
            $date = Carbon::yesterday()->subDays($i)->format('Y-m-d');
            $result['dates'][] = $date;
            $result['sales_user'][]
                = $this->getProductSales($products, $vendorCode, $date);
            $result['sales_top36'][]
                = $this->getTop36Sales($products, $vendorCode, $date);
            $result['avg_position_category_user'][]
                = $this->getFieldProduct($products, $vendorCode,
                'position_category', $date);
            $result['optimization'][]
                = $this->getFieldProduct($products, $vendorCode,
                'optimization', $date);
            $result['feedbacks_top36'][]
                = $this->getFieldTop36($products, $vendorCode, 'comments_avg',
                $date);
            $result['feedbacks_user'][]
                = $this->getFieldProduct($products, $vendorCode, 'comments',
                $date);
            $result['avg_position_search_top36'][] = null;
            $result['avg_position_search_user'][]
                = $this->getFieldProduct($products, $vendorCode,
                'position_search', $date);;
            $result['avg_optimization'][] = null;
            $result['rating_user'][]
                = $this->getFieldProduct($products, $vendorCode, 'rating',
                $date);
            $result['rating_top36'][]
                = $this->getFieldTop36($products, $vendorCode, 'rating_avg',
                $date);
            $result['images_user'][]
                = $this->getFieldProduct($products, $vendorCode, 'images',
                $date);
            $result['images_top36'][]
                = $this->getFieldTop36($products, $vendorCode, 'images_avg',
                $date);
            $result['authorship_protection'][]
                = $this->getFieldProduct($products, $vendorCode, 'escrow',
                $date);;
            // массив проблем такой же как у графиков, 1 элемент - 1 день
            $result['troubles'][] = $historyRepository->getTroubles($result,
                $date);
        }
        // Массив проблем по товару, в виде текстовых описаний. Без разбивки по дням.
        $result['troubles_summary']
            = $historyRepository->getTroublesSummary($result);

        return $result;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function getWeightData(array $data): array
    {
        foreach ($data as $key => $item) {
            $data[$key]['weight'] = $this->getWeightItemData($item);
        }

        return $data;
    }

    /**
     * @param array $item
     *
     * @return int
     */
    public function getWeightItemData(array $item): int
    {
        $weight = 0;

        if ($item['sales']['trend'] >= 0) {
            $weight += ActionParams::WEIGHT_SALES;
        }

        if ($item['optimization']['trend'] >= 0) {
            $weight += ActionParams::WEIGHT_OPTIMIZATION;
        }

        if ($item['rating']['user']['trend'] >= 0) {
            $weight += ActionParams::WEIGHT_RATING;
        }

        if ($item['avg_position_search']['user']['trend'] >= 0) {
            $weight += ActionParams::WEIGHT_POSITION_SEARCH;
        }

        if ($item['avg_position_category']['user']['trend'] >= 0) {
            $weight += ActionParams::WEIGHT_POSITION_CATEGORY;
        }

        if ($item['images_count']['user']['trend'] >= 0) {
            $weight += ActionParams::WEIGHT_COUNT_IMAGES;
        }

        if ($item['authorship_protection']['trend'] >= 0) {
            $weight += ActionParams::WEIGHT_ESCROW;
        }

        return $weight;
    }

    /**
     * Get escrow percent for wildberries account
     *
     * @param StdClass $product
     * @param $nmId
     *
     * @return int
     */
    public function getEscrowPercentForNomenclature(
        StdClass $product,
        $nmId
    ): int {
        $images = $this->getImagesByNmId($product, $nmId);
        if (!count($images)) {
            return 0;
        }
        $imageHashes = [];
        foreach ($images as $image) {
            $hash = $this->getImageHash($image);
            if ($hash) {
                $imageHashes[] = current($hash);
            }
        }

        $hashes = (new EscrowHashRepository())
            ->getEscrowImageHashes($product->id, $nmId);
        $intersect = array_intersect($hashes, $imageHashes);
        $intersectCount = count($intersect);
        $percent = ($intersectCount / count($images)) * 100;

        return (int) $percent;
    }

    /**
     * Get images by nmid wildberries
     *
     * @param $product
     * @param $nmid
     *
     * @return array|null
     */
    public function getImagesByNmId($product, $nmid): ?array
    {
        $images = [];
        try {
            foreach (json_decode($product->data_nomenclatures) as $data) {
                if ($data->nmId != $nmid) {
                    continue;
                }
                foreach ($data->addin as $addin) {
                    if ($addin->type !== 'Фото') {
                        continue;
                    }
                    foreach ($addin->params as $param) {
                        $images[] = $param->value;
                    }
                }
            }
        } catch (\Exception $exception) {
            report($exception);

            return [];
        }

        return $images;
    }

    /**
     * Get image hash from url or path of image
     *
     * @param $image
     *
     * @return array
     */
    protected function getImageHash($image): array|null
    {
        $basename = basename($image);
        try {
            $hash = hash_file('sha512', $image);
        } catch (\Exception $exception) {
            report($exception);

            return null;
        }

        return [$basename => $hash];
    }

    /**
     * Process array of images to hashes
     *
     * @param $images
     * @param $hashes
     *
     * @return array|null
     */
    protected function getImageHashes($images, $hashes = []): array|null
    {
        if (!isset($images)) {
            return null;
        }
        foreach ($images as $image) {
            $hash = self::getImageHash($image);
            if ($hash) {
                $hashes[] = self::getImageHash($image);
            } else {
                return null;
            }
        }

        return $hashes;
    }

    /**
     * @param StdClass $product
     *
     * @return VaCategoriesFilter|null
     */
    public function getInfoByCategoriesFilter(StdClass $product
    ): VaCategoriesFilter|null {
        return VaCategoriesFilter::query()->select('web_id', 'subject_id')
            ->leftJoin('va_categories', 'va_categories.id', '=',
                'va_categories_filters.va_category_id')
            ->where('va_categories.name', '=', $product->object)
            ->where('va_categories.parent', '=', $product->parent)->first();
    }

    /**
     * @param array $weightData
     *
     * @return array
     */
    public function getTroublesProduct(array $weightData): array
    {
        $collectData = collect($weightData);
        $minWeight = $collectData->min('weight');
        $minOptimisationValue = $collectData->where('weight', '=', $minWeight)
            ->min('optimization.value');

        return $collectData->where('optimization.value', '=',
            $minOptimisationValue)->first();
    }
}
