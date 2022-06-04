<?php

namespace App\Services;

use App\Constants\Constants;
use App\DataTransferObjects\Services\OzonPerformance\Campaign\CampaignDTO;
use App\DataTransferObjects\Services\OzonPerformance\Campaign\CampaignListDTO;
use App\Enums\OzonPerformance\Campaign\CampaignState;
use App\Helpers\OzonHelper;
use App\Models\Campaign;
use App\Models\CampaignKeyword;
use App\Models\CampaignKeywordStatistic;
use App\Models\CampaignPageType;
use App\Models\CampaignProduct;
use App\Models\CampaignProductStatistic;
use App\Models\CampaignStatistic;
use App\Models\CampaignStatus;
use App\Models\CampaignType;
use App\Models\Category;
use App\Models\Group;
use App\Models\Keyword;
use App\Models\KeywordPopularity;
use App\Models\StatisticReport;
use App\Models\Status;
use App\Models\StopWord;
use App\Repositories\V2\Product\CategoryRepository;
use App\Repositories\V2\Product\ProductRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseService
{

    const CAMPAIGN_QUANTITY_PER_UPSERT = 100;

    protected static array $productsStatistics = [];

    protected static array $campaignStatistics = [];

    protected static array $productsPopularitiesSums = [];

    protected static array $campaignPopularitiesSums = [];


    /**
     * Get web-app query builder
     *
     * @param  string  $tableName
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getWabTableQuery(string $tableName)
    {
        return DB::connection('wab')->table($tableName);
    }


    /**
     * Save campaigns list
     *
     * @param  CampaignListDTO  $campaignList
     * @param  integer  $accountId
     *
     * @return int
     */
    public static function saveCampaignList(CampaignListDTO $campaignList, $accountId): int
    {
        $statuses      = CampaignStatus::select(['id', 'ozon_code'])->get()->keyBy('ozon_code');
        $defaultStatus = $statuses[CampaignState::CAMPAIGN_STATE_ARCHIVED()->getValue()];
        $campaignTypes = CampaignType::select(['id', 'code'])->get()->keyBy('code');

        $campaignDefaultData = array_fill_keys(Schema::getColumnListing('campaigns'), null);

        $totalUpsertCount = 0;

        $campaignListChunks = array_chunk($campaignList->list, self::CAMPAIGN_QUANTITY_PER_UPSERT);
        foreach ($campaignListChunks as $campaignListChunk) {
            $campaigns   = [];
            $dateTimeNow = Carbon::now();
            /** @var  CampaignDTO $campaignData */
            foreach ($campaignListChunk as $chunkNumber => $campaignData) {
                $status = $statuses->get($campaignData->state->getValue(), $defaultStatus);

                $campaignId = $campaignData->id;
                $campaign   = Campaign::find($campaignData->id)?->toArray();

                if (!$campaign) {
                    $type     = $campaignTypes->get($campaignData->advObjectType->getValue());
                    $campaign = [
                        'id'         => $campaignId,
                        'ozon_id'    => $campaignId,
                        'account_id' => $accountId,
                        'type_id'    => $type?->id,
                        'type'       => $campaignData->advObjectType->getValue(),
                        'created_at' => $dateTimeNow,
                    ];
                }

                $campaign['name']               = $campaignData->title;
                $campaign['budget']             = $campaignData->dailyBudget;
                $campaign['campaign_status_id'] = $status->id;
                $campaign['state']              = $campaignData->state;
                $campaign['last_ozon_sync']     = $dateTimeNow;

                $campaign['start_date'] = $campaignData->fromDate;
                $campaign['end_date']   = $campaignData->toDate;
                $campaign['updated_at'] = $dateTimeNow;

                $campaigns[] = $campaign + $campaignDefaultData;
            }
            printf('Saving %d campaigns for account %d'.PHP_EOL, count($campaigns), $accountId);
            $upsertCount = self::saveCampaigns($campaigns);
            printf('%d upsertions were made'.PHP_EOL, $upsertCount);
            $totalUpsertCount += $upsertCount;
        }

        printf('%d upsertions were made in total'.PHP_EOL, $totalUpsertCount);

        return $totalUpsertCount;
    }


    /**
     * Сохранение товаров
     *
     * @param  array  $products
     * @param $account
     */
    public static function saveProductsList($products, $account)
    {
        foreach ($products as $product) {
            foreach ($product->sources as $sku) {
                $product = Product::query()
                    ->where('product_id', $product->id)
                    ->where('sku', $sku->sku)
                    ->where('account_id', $account->id)
                    ->first();

                if (!$product) {
                    $product             = new Product();
                    $product->product_id = $product->id;
                    $product->offer_id   = $product->offer_id;
                    $product->name       = $product->name;
                    $product->sku        = $sku->sku;
                    $product->account_id = $account->id;
                }

                $category = Category::query()->where('ozon_id', $product->category_id)->first();
                if (!$category) {
                    echo 'Not Found Category '.$product->category_id."\r\n";
                }

                $status = Status::query()->where('ozon_code', $product->state)->first();
                if (!$status) {
                    echo 'Not Found Status '.$product->state."\r\n";
                }

                $product->category_id       = $category ? $category->id : null;
                $product->product_status_id = $status ? $status->id : null;

                $res = $product->save();
                if (!$res) {
                    echo 'Error Save Product '.$product->sku.' ('.$product->name.')'."\r\n";
                    continue;
                }
            }
        }
    }


    /**
     * Сохранение товаров в РК
     *
     * @param  array  $products
     * @param  Campaign  $campaign
     * @param $account
     */
    public static function saveCampaignProducts($products, $campaign, $account)
    {
        $existedCampaignProducts = $campaign->campaignProducts();
        $existedGroups           = $campaign->groups();
        $syncProducts            = [];
        $syncGroups              = [];

        foreach ($products as $product) {
            // Товар в РК
            $campaignProduct = (clone $existedCampaignProducts)->where('product_id', $product->sku)->first();
            $statusId        = $campaign->campaignStatus->code == CampaignStatus::ACTIVE ? Status::ACTIVE : Status::NOT_ACTIVE;

            if (!$campaignProduct) {
                $campaignProduct              = new CampaignProduct();
                $campaignProduct->product_id  = $product->sku;
                $campaignProduct->campaign_id = $campaign->id;
            }

            $campaignProduct->status_id = $statusId;
            $saveResult                 = $campaignProduct->save();
            if (!$saveResult) {
                echo 'Error Save Campaign Product '.$product->sku.' ('.$campaign->name.')'."\r\n";
                continue;
            }

            $syncProducts[] = $campaignProduct->id;

            // Группа
            $group = null;
            if ($product->groupId) {
                $group = (clone $existedGroups)->where('ozon_id', $product->groupId)->first();

                if (!$group) {
                    $group              = new Group();
                    $group->ozon_id     = $product->groupId;
                    $group->campaign_id = $campaign->id;
                }

                $group->status_id = $statusId;
                $saveResult       = $campaign->groups()->save($group);
                if (!$saveResult) {
                    echo 'Error Save Group '.$product->groupId."\r\n";
                    continue;
                }

                $syncGroups[] = $group->id;
            }

            $campaignProduct->group_id = $group ? $group->id : null;
            $saveResult                = $campaignProduct->save();
            if (!$saveResult) {
                echo 'Error Update Campaign Product '.$product->sku.' ('.$campaign->name.')'."\r\n";
                continue;
            }

            // Минус-слова
            $syncStopWords = [];
            foreach ($product->stopWords as $ozonStopWord) {
                // Ищем минус-слово
                $stopWord = StopWord::query()->where('name', $ozonStopWord)->first();

                if (!$stopWord) {
                    $stopWord       = new StopWord();
                    $stopWord->name = $ozonStopWord;
                    $saveResult     = $stopWord->save();
                    if (!$saveResult) {
                        echo 'Error stop word save: '.$ozonStopWord."\r\n";
                        continue;
                    }
                }

                // Данные о связи
                $data                         = ['group_id' => $product->groupId ? $group->id : null, 'status_id' => $statusId];
                $syncStopWords[$stopWord->id] = $data;
            }

            // Привязываем к товару в РК и к группе
            $campaignProduct->stopWords()->syncWithoutDetaching($syncStopWords);

            // Ключевые слова
            $syncKeywords = [];
            foreach ($product->phrases as $ozonKeyword) {
                // Ищем ключевое слово в категории
                $productCategory = CategoryRepository::getProductCategory($product->sku, $account->id);
                $topCategory     = $productCategory ? CategoryRepository::getTopCategory($productCategory->external_id) : null;
                $categoryId      = $topCategory ? $topCategory->external_id : null;

                $keyword = Keyword::query()
                    ->where('name', $ozonKeyword->phrase)
                    ->where('category_id', $categoryId)
                    ->first();

                if (!$keyword) {
                    $keyword              = new Keyword();
                    $keyword->name        = $ozonKeyword->phrase;
                    $keyword->category_id = $categoryId;
                    $saveResult           = $keyword->save();

                    if (!$saveResult) {
                        echo sprintf('Error keyword save: %s %s', $ozonKeyword->phrase, PHP_EOL);
                        continue;
                    }
                }

                // Данные о связи
                $statusId = $ozonKeyword->bid > 0 && $campaignProduct->status_id == Status::ACTIVE ? Status::ACTIVE : Status::NOT_ACTIVE;
                $bid      = $ozonKeyword->bid / OzonHelper::BUDGET_COEFFICIENT;

                $data                       = ['group_id' => $product->groupId ? $group->id : null, 'status_id' => $statusId, 'bid' => $bid];
                $syncKeywords[$keyword->id] = $data;
            }

            // Привязываем к товару в РК и к группе
            $campaignProduct->keywords()->syncWithoutDetaching($syncKeywords);

            $campaignProduct->campaignKeywords()
                ->whereNotIn('keyword_id', array_keys($syncKeywords))
                ->update(['status_id' => Status::DELETED]);
        }

        // Всем товарам, которые не пришли от Озона, присваиваем статус "Удалено"
        $existedCampaignProducts->whereNotIn('id', $syncProducts)->update(['status_id' => Status::DELETED]);

        // Всем группам, которые не пришли от Озона, присваиваем статус "Удалено"
        $existedGroups->whereNotIn('id', $syncGroups)->update(['status_id' => Status::DELETED]);
    }


    /**
     * Сохранить статистику
     *
     * @param  array  $reports
     * @param  int  $campaignOzonId
     * @param $account
     */
    public static function saveStatistics(array $reports, $campaignOzonId, $account)
    {
        static::$productsStatistics = [];
        static::$campaignStatistics = [];

        $campaign = Campaign::query()
            ->where('ozon_id', $campaignOzonId)
            ->where('account_id', $account->id)
            ->with(['campaignStatus', 'campaignPageType'])
            ->first();

        $statusId             = $campaign && $campaign->campaignStatus->code == CampaignStatus::ACTIVE ? Status::ACTIVE : Status::NOT_ACTIVE;
        $campaignPageTypeName = $campaign && $campaign->campaignPageType ? $campaign->campaignPageType->name : null;
        $dataStationType      = null;

        foreach ($reports as $reportRow) {
            $date = Carbon::parse($reportRow[0]);

            // костыль на случай разных отчетов
            $shift = count($reportRow) == 15 ? 0 : 1;

            // Сбор данных из отчета
            $report          = new StatisticReport();
            $report->date    = $date->toDateString();
            $report->sku     = $reportRow[1];
            $report->name    = $reportRow[2];
            $report->price   = (double) str_replace(",", ".", $reportRow[3]);
            $report->type    = $reportRow[4];
            $report->keyword = $shift == 0 ? $reportRow[5] : null;
            $report->shows   = (int) $reportRow[6 - $shift];
            $report->clicks  = (int) $reportRow[7 - $shift];
            $report->ctr     = (double) str_replace(",", ".", $reportRow[8 - $shift]);
            $report->bid     = (double) $reportRow[9 - $shift];
            $report->cost    = (double) str_replace(",", ".", $reportRow[10 - $shift]);
            $report->orders  = (int) $reportRow[11 - $shift];
            $report->profit  = (double) str_replace(",", ".", $reportRow[12 - $shift]);

            // Сохраняем статистику только по РК типа "Поиск"
            if ($report->type == Constants::CAMPAIGN_SEARCH) {
                // Уровень кампании
                static::saveCampaignStatistic($report, $campaign->id);

                // Уровень товара
                $campaignProduct = static::saveProductStatistic($report, $campaign->id, $statusId, $account);

                // Содержит колонку с ключевым словом
                if (!is_null($report->keyword)) {
                    // Уровень ключевых слов
                    static::saveKeywordStatistic($report, $campaignProduct, $statusId);
                }
            }

            $dataStationType = $report->type;
        }

        if ($dataStationType && $campaignPageTypeName != $dataStationType) {
            $pageType = CampaignPageType::query()->where('name', $dataStationType)->first();
            if ($pageType) {
                $campaign->campaignPageType()->associate($pageType);
                $campaign->save();
            }
        }
    }


    /**
     * Сохранение статистики по рекламной кампании
     *
     * @param  StatisticReport  $report
     * @param  int  $campaignId
     *
     * @return boolean
     */
    public static function saveCampaignStatistic(StatisticReport $report, $campaignId)
    {
        $key = $campaignId.'.'.$report->date;

        if (!isset(static::$campaignStatistics[$key])) {
            $campaignStatistic = CampaignStatistic::query()
                ->where('campaign_id', $campaignId)
                ->whereDate('date', $report->date)
                ->first();

            if (!$campaignStatistic) {
                $campaignStatistic              = new CampaignStatistic();
                $campaignStatistic->campaign_id = $campaignId;
                $campaignStatistic->date        = $report->date;
            }

            $campaignStatistic->cost   = 0;
            $campaignStatistic->shows  = 0;
            $campaignStatistic->clicks = 0;
            $campaignStatistic->orders = 0;
            $campaignStatistic->profit = 0;

            static::$campaignStatistics[$key] = $campaignStatistic;
        } else {
            $campaignStatistic = &static::$campaignStatistics[$key];
        }

        $campaignStatistic->cost   += $report->cost;
        $campaignStatistic->shows  += $report->shows;
        $campaignStatistic->clicks += $report->clicks;
        $campaignStatistic->orders += $report->orders;
        $campaignStatistic->profit += $report->profit;

        return $campaignStatistic->save();
    }


    /**
     * Сохранение статистики по товару
     *
     * @param  StatisticReport  $report
     * @param  int  $campaignId
     * @param  int  $statusId
     * @param $account
     *
     * @return CampaignProduct|false
     */
    protected static function saveProductStatistic(StatisticReport $report, $campaignId, $statusId, $account)
    {
        // Находим товар
        $product = (new ProductRepository())->getProduct($report->sku, $account);

        if (!$product) {
            echo 'Error get product, sku: '.$report->sku."\r\n";

            return false;
        }

        // Обновляем его, если он создан не сегодня
        if ($product->created_at->toDate() == Carbon::today()->toDate()) {
            $arUpdate = [];
            if ($report->name) {
                $arUpdate['name'] = $report->name;
            }
            if ($report->price) {
                $arUpdate['price'] = $report->price;
            }

            if (!empty($arUpdate)) {
                $product->update($arUpdate);
            }
        }

        // Связь Товар - РК
        $campaignProduct = $product->campaignProducts()->where('campaign_id', $campaignId)->first();

        if (!$campaignProduct) {
            $campaignProduct              = new CampaignProduct();
            $campaignProduct->campaign_id = $campaignId;
            $campaignProduct->product_id  = $product->id;
        }
        $campaignProduct->status_id = $statusId;
        $campaignProduct->save();

        // Накопление статистики
        $key = $campaignProduct->id.'.'.$report->date;

        if (!isset(static::$productsStatistics[$key])) {
            $productStatistic = CampaignProductStatistic::query()
                ->where('campaign_product_id', $campaignProduct->id)
                ->whereDate('date', $report->date)
                ->first();

            if (!$productStatistic) {
                $productStatistic                      = new CampaignProductStatistic();
                $productStatistic->campaign_product_id = $campaignProduct->id;
                $productStatistic->date                = $report->date;
            }

            $productStatistic->cost   = 0;
            $productStatistic->shows  = 0;
            $productStatistic->clicks = 0;
            $productStatistic->orders = 0;
            $productStatistic->profit = 0;

            static::$productsStatistics[$key] = $productStatistic;
        } else {
            $productStatistic = &static::$productsStatistics[$key];
        }

        $productStatistic->cost   += $report->cost;
        $productStatistic->shows  += $report->shows;
        $productStatistic->clicks += $report->clicks;
        $productStatistic->orders += $report->orders;
        $productStatistic->profit += $report->profit;

        $productStatistic->save();

        return $campaignProduct;
    }


    /**
     * Сохранение статистики по ключевому слову
     *
     * @param  StatisticReport  $report
     * @param  CampaignProduct  $campaignProduct
     * @param  int  $statusId
     *
     * @return boolean
     */
    protected static function saveKeywordStatistic(StatisticReport $report, $campaignProduct, $statusId)
    {
        // Ищем ключевое слово в категории
        $productCategory = $campaignProduct->product->category;
        $topCategory     = $productCategory ? $productCategory->topCategory() : null;
        $categoryId      = $topCategory ? $topCategory->id : null;

        $keyword = Keyword::query()
            ->where('name', $report->keyword)
            ->where('category_id', $categoryId)
            ->first();

        if (!$keyword) {
            $keyword              = new Keyword();
            $keyword->name        = $report->keyword;
            $keyword->category_id = $categoryId;
            $keyword->save();
        }

        $campaignKeyword = $campaignProduct->campaignKeywords()->where('keyword_id', $keyword->id)->first();
        $statusId        = $statusId == Status::ACTIVE && $report->bid > 0 ? Status::ACTIVE : Status::NOT_ACTIVE;

        if (!$campaignKeyword) {
            $campaignKeyword                      = new CampaignKeyword();
            $campaignKeyword->campaign_product_id = $campaignProduct->id;
            $campaignKeyword->group_id            = $campaignProduct->group_id;
            $campaignKeyword->keyword_id          = $keyword->id;
            $campaignKeyword->status_id           = $statusId;
            $campaignKeyword->bid                 = $report->bid;
            $campaignKeyword->save();
        } else {
            CampaignKeyword::query()
                ->where('campaign_product_id', $campaignProduct->id)
                ->where('keyword_id', $keyword->id)
                ->update([
                    'status_id' => $statusId,
                    'bid'       => $report->bid,
                ]);
        }

        // Сохранение статистики
        $keywordStatistic = CampaignKeywordStatistic::query()
            ->where('campaign_keyword_id', $campaignKeyword->id)
            ->whereDate('date', $report->date)
            ->first();

        if (!$keywordStatistic) {
            $keywordStatistic                      = new CampaignKeywordStatistic();
            $keywordStatistic->campaign_keyword_id = $campaignKeyword->id;
            $keywordStatistic->date                = $report->date;
        }

        $keywordStatistic->cost   = $report->cost;
        $keywordStatistic->shows  = $report->shows;
        $keywordStatistic->clicks = $report->clicks;
        $keywordStatistic->ctr    = $report->ctr;
        $keywordStatistic->orders = $report->orders;
        $keywordStatistic->profit = $report->profit;

        return $keywordStatistic->save();
    }


    /**
     * Сохранить популярность из ВП
     *
     * @param  object  $popularities
     */
    public static function savePopularities($popularities)
    {
        // Сохраняем в таблицу со статистикой по ключевикам
        foreach ($popularities as $keywordPopularities) {
            $keywordId = $keywordPopularities->keywordId;

            foreach ($keywordPopularities->popularities as $date => $popularity) {
                // Сохраняем в статистику ключевика
                KeywordPopularity::query()
                    ->firstOrCreate([
                        'keyword_id' => $keywordId,
                        'date'       => $date,
                    ])->update([
                        'popularity' => $popularity,
                        'updated_at' => Carbon::now()
                    ]);
            }
        }
    }


    /**
     * Обновить популярность в таблицах статистики
     *
     * @param  Campaign  $campaign
     * @param  string  $dateFrom
     * @param  string  $dateTo
     *
     * @return mixed
     */
    public static function updatePopularitiesInStatistics($campaign, $dateFrom, $dateTo)
    {
        self::$productsPopularitiesSums = [];
        self::$campaignPopularitiesSums = [];

        $lastSavedDate = null;

        // Сохраняем в таблицу со статистикой по ключевикам
        $campaignProducts = $campaign->campaignProducts()->whereNotIn('status_id', [Status::DELETED])->get();
        foreach ($campaignProducts as $campaignProduct) {
            $campaignKeywords = $campaignProduct->campaignKeywords()->whereNotIn('status_id', [Status::DELETED])->get();
            foreach ($campaignKeywords as $campaignKeyword) {
                $keywordPopularities = KeywordPopularity::query()
                    ->where('keyword_id', $campaignKeyword->keyword_id)
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->get();

                foreach ($keywordPopularities as $keywordPopularity) {
                    // Сохраняем в статистику ключевика в РК
                    self::saveKeywordPopularity($campaignKeyword, $keywordPopularity->date,
                        $keywordPopularity->popularity);

                    if ($lastSavedDate < $keywordPopularity->date) {
                        $lastSavedDate = $keywordPopularity->date;
                    }
                }
            }
        }

        // Уровень товаров
        self::saveProductsPopularity();

        // Уровень рекламных кампаний
        self::saveCampaignsPopularity();

        return $lastSavedDate;
    }


    /**
     * Сохранить популярность по ключевым словам
     *
     * @param  CampaignKeyword  $campaignKeyword
     * @param  string  $date
     * @param  integer  $popularity
     */
    protected static function saveKeywordPopularity($campaignKeyword, $date, $popularity)
    {
        // Если дата меньше даты создания ключевика
        if ($campaignKeyword->created_at && $date < $campaignKeyword->created_at->toDateString()) {
            return;
        }

        $campaignKeywordsStatistic = $campaignKeyword->statistics()->whereDate('date', $date)->first();

        if (!$campaignKeywordsStatistic) {
            $campaignKeywordsStatistic                      = new CampaignKeywordStatistic();
            $campaignKeywordsStatistic->campaign_keyword_id = $campaignKeyword->id;
            $campaignKeywordsStatistic->date                = $date;
        }

        $campaignKeywordsStatistic->popularity = $popularity;
        $campaignKeywordsStatistic->save();

        // Суммируем информацию по товарам
        $campaignProductId = $campaignKeyword->campaign_product_id;
        if (!isset(self::$productsPopularitiesSums[$campaignProductId][$date])) {
            self::$productsPopularitiesSums[$campaignProductId][$date] = 0;
        }
        self::$productsPopularitiesSums[$campaignProductId][$date] += $campaignKeywordsStatistic->popularity ?? 0;
    }


    /**
     * Сохранить популярность по товарам
     */
    protected static function saveProductsPopularity()
    {
        foreach (self::$productsPopularitiesSums as $campaignProductId => $datesPopularities) {
            $campaignProduct           = CampaignProduct::find($campaignProductId);
            $campaignProductStatistics = $campaignProduct->statistics();
            $campaignId                = $campaignProduct->campaign_id;

            foreach ($datesPopularities as $date => $popularity) {
                // Если дата меньше даты создания товара
                if ($campaignProduct->created_at && $date < $campaignProduct->created_at->toDateString()) {
                    continue;
                }

                $campaignProductStatistic = (clone $campaignProductStatistics)->whereDate('date', $date)->first();

                // Если за этот день по какой-то причине не было статистики, создаем
                if (!$campaignProductStatistic) {
                    $campaignProductStatistic                      = new CampaignProductStatistic();
                    $campaignProductStatistic->campaign_product_id = $campaignProductId;
                    $campaignProductStatistic->date                = $date;
                }

                $campaignProductStatistic->popularity = $popularity;
                $campaignProductStatistic->save();

                // Суммируем информацию по кампаниям
                if (!isset(self::$campaignPopularitiesSums[$campaignId][$date])) {
                    self::$campaignPopularitiesSums[$campaignId][$date] = 0;
                }
                self::$campaignPopularitiesSums[$campaignId][$date] += $campaignProductStatistic->popularity;
            }
        }
    }


    /**
     * Сохранить популярность по кампаниям
     */
    protected static function saveCampaignsPopularity()
    {
        foreach (self::$campaignPopularitiesSums as $campaignId => $datesPopularities) {
            $campaign           = Campaign::find($campaignId);
            $campaignStatistics = $campaign->statistics();

            foreach ($datesPopularities as $date => $popularity) {
                // Если дата меньше даты создания компании
                if ($campaign->created_at && $date < $campaign->created_at->toDateString()) {
                    continue;
                }

                $campaignStatistic = (clone $campaignStatistics)->whereDate('date', $date)->first();

                // Если за этот день по какой-то причине не было статистики, создаем
                if (!$campaignStatistic) {
                    $campaignStatistic              = new CampaignStatistic();
                    $campaignStatistic->campaign_id = $campaignId;
                    $campaignStatistic->date        = $date;
                }

                $campaignStatistic->popularity = $popularity;
                $campaignStatistic->save();
            }
        }
    }


    /**
     * @param  array  $campaigns
     *
     * @return int
     */
    private static function saveCampaigns(array $campaigns): int
    {
        $upsertCount = Campaign::query()->upsert(
            $campaigns,
            ['id', 'ozon_id'],
            [
                'id', 'ozon_id', 'account_id', 'type_id', 'type_id',
                'name', 'budget', 'campaign_status_id', 'start_date', 'end_date',
                'created_at', 'updated_at', 'last_ozon_sync'
            ]
        );

        return $upsertCount;
    }
}
