<?php

namespace App\Actions\Words;

use App\Models\CampaignProduct;
use App\Models\CampaignKeyword;
use App\Models\CampaignStatus;
use App\Models\CampaignStopWord;
use App\Models\Keyword;
use App\Models\Status;
use App\Models\StopWord;
use App\Repositories\Frontend\Keyword\CampaignKeywordRepository;
use App\Repositories\Frontend\Keyword\KeywordRepository;
use App\Repositories\Frontend\Stopword\CampaignStopWordRepository;
use App\Repositories\Frontend\Stopword\StopWordRepository;
use App\Repositories\V2\Campaign\CampaignProductRepository;
use App\Tasks\Products\GetGroupedProductsTask;
use App\Tasks\Words\UpdateOzonCampaignProductWordsTask;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class SaveCampaignProductWordsSubAction
 *
 * @package App\Actions\Words
 */
class SaveCampaignProductWordsSubAction
{
    /** @var KeywordRepository $keywordRepository */
    protected KeywordRepository $keywordRepository;

    /** @var StopWordRepository $stopwordRepository */
    protected StopWordRepository $stopwordRepository;

    /** @var CampaignKeywordRepository $campaignKeywordRepository */
    protected CampaignKeywordRepository $campaignKeywordRepository;

    /** @var CampaignStopWordRepository $campaignStopwordRepository */
    protected CampaignStopWordRepository $campaignStopwordRepository;

    /** @var CampaignProductRepository $campaignProductRepository */
    protected CampaignProductRepository $campaignProductRepository;

    /**
     * SaveCampaignProductWordsSubAction constructor.
     */
    public function __construct()
    {
        $this->keywordRepository = new KeywordRepository();
        $this->stopwordRepository = new StopWordRepository();
        $this->campaignKeywordRepository = new CampaignKeywordRepository();
        $this->campaignStopwordRepository = new CampaignStopWordRepository();
        $this->campaignProductRepository = new CampaignProductRepository();
    }

    /**
     * @param int $campaignProductId
     * @param array $words
     * @return array[]
     */
    #[ArrayShape([
        'missedKeywords' => "array",
        'missedStopwords' => "array",
        'savedCampaignKeywords' => "array",
        'savedCampaignStopwords' => "array",
        'existKeywords' => "array",
        'existStopwords' => "array",
        'extraLimitKeywords' => "array",
        'extraLimitStopwords' => "array", 'errors' => "array"
    ])]
    public function run(int $campaignProductId, array $words): array
    {
        $missedKeywords = [];
        $savedKeywords = [];
        $existKeywords = [];
        $missedStopwords = [];
        $savedStopwords = [];
        $existStopwords = [];
        $extraLimitKeywords = [];
        $extraLimitStopwords = [];
        $campaignProducts = (new GetGroupedProductsTask())->run($campaignProductId);

        foreach ($campaignProducts as $product) {
            $productKeywordsCount = $this->campaignKeywordRepository->getUsableKeywordsCount($product->id);

            if (isset($words['keywords'])) {
                foreach ($words['keywords'] as $keywordName) {
                    if ($productKeywordsCount >= 100) {
                        $extraLimitKeywords[] = $keywordName;
                    } else if (!is_null($keywordName) && $this->validateWord($keywordName)) {
                        $exists = false;
                        $keyword = $this->keywordRepository->getByName($keywordName);

                        if (!$keyword) {
                            $keyword = new Keyword(['name' => $keywordName]);
                            $keyword->save();
                        }

                        $campaignKeyword = $this->campaignKeywordRepository->getByRelation($product->id, $keyword->id);

                        if ($campaignKeyword) {
                            if ($campaignKeyword->status_id !== Status::ACTIVE) {
                                $campaignKeyword->status_id = Status::ACTIVE;
                                $campaignKeywordResult = $campaignKeyword->save();
                                $productKeywordsCount++;
                            } else {
                                $exists = true;
                            }
                        } else {
                            $campaignKeyword = new CampaignKeyword();
                            $campaignKeyword->campaign_product_id = $product->id;
                            $campaignKeyword->group_id = $product->group_id;
                            $campaignKeyword->status_id = Status::ACTIVE;
                            $campaignKeyword->keyword_id = $keyword->id;
                            $campaignKeyword->bid = 35;
                            $campaignKeywordResult = $campaignKeyword->save();
                            $productKeywordsCount++;
                        }

                        if ($exists) {
                            $existKeywords[] = $campaignKeyword->id;
                        } else if (!$campaignKeywordResult) {
                            $missedKeywords[] = $keywordName;
                        } else {
                            $savedKeywords[] = $campaignKeyword->id;
                        }
                    } else {
                        $missedKeywords[] = $keywordName;
                    }
                }
            }
        }

        foreach ($campaignProducts as $product) {
            $productStopwordsCount = $this->campaignStopwordRepository->getUsableStopwordsCount($product->id);

            if (isset($words['stopwords'])) {
                foreach ($words['stopwords'] as $stopwordName) {
                    if ($productStopwordsCount >= 100) {
                        $extraLimitStopwords[] = $stopwordName;
                    } else if (!is_null($stopwordName) && $this->validateWord($stopwordName)) {
                        $exists = false;
                        $stopword = $this->stopwordRepository->getByName($stopwordName);

                        if (!$stopword) {
                            $stopword = new StopWord(['name' => $stopwordName]);
                            $stopword->save();
                        }

                        $campaignStopword = $this->campaignStopwordRepository->getByRelation($product->id, $stopword->id);

                        if ($campaignStopword) {
                            if ($campaignStopword->status_id !== Status::ACTIVE) {
                                $campaignStopword->status_id = Status::ACTIVE;
                                $campaignStopwordResult = $campaignStopword->save();
                                $productStopwordsCount++;
                            } else {
                                $exists = true;
                            }
                        } else {
                            $campaignStopword = new CampaignStopWord();
                            $campaignStopword->campaign_product_id = $product->id;
                            $campaignStopword->group_id = $product->group_id;
                            $campaignStopword->stop_word_id = $stopword->id;
                            $campaignStopwordResult = $campaignStopword->save();
                            $productStopwordsCount++;
                        }

                        if ($exists) {
                            $existStopwords[] = $campaignStopword->id;
                        } else if (!$campaignStopwordResult) {
                            $missedStopwords[] = $stopwordName;
                        } else {
                            $savedStopwords[] = $campaignStopword->id;
                        }
                    } else {
                        $missedStopwords[] = $stopwordName;
                    }
                }
            }
        }

        /** @var CampaignProduct $campaignProduct */
        $campaignProduct = $this->campaignProductRepository->getItem($campaignProductId);
        if ($campaignProduct->campaign->campaignStatus->code === CampaignStatus::ACTIVE) {
            (new UpdateOzonCampaignProductWordsTask())->run($campaignProductId);
        }

        return [
            'missedKeywords' => $missedKeywords,
            'missedStopwords' => $missedStopwords,
            'savedCampaignKeywords' => $savedKeywords,
            'savedCampaignStopwords' => $savedStopwords,
            'existKeywords' => $existKeywords,
            'existStopwords' => $existStopwords,
            'extraLimitKeywords' => $extraLimitKeywords,
            'extraLimitStopwords' => $extraLimitStopwords,
            'errors' => []
        ];
    }

    /**
     * @param string $str
     * @return bool
     */
    private function validateWord(string $str): bool
    {
        $word = str_replace(' ', '', $str);
        return !(mb_strlen($word) < 2 ||
            preg_match('/[^a-zA-ZА-Яа-яЁё1-9]/u', str_replace(' ', '', $word)));
    }
}
