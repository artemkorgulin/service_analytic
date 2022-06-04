<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Class CampaignKeyword
 *
 * @package App\Models
 *
 * @property int $campaign_product_id
 * @property int $group_id
 * @property int $keyword_id
 * @property int $status_id
 * @property int $bid
 *
 * @property-read CampaignProduct $campaignProduct
 * @property-read Keyword $keyword
 */
class CampaignKeyword extends Model
{
    const DEFAULT_KEYWORD_BID = 35;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'campaign_product_id',
        'group_id',
        'keyword_id',
        'status_id',
        'bid',
    ];

    public $timestamps = false;

    /**
     * Товар в РК
     *
     * @return BelongsTo
     */
    public function campaignProduct()
    {
        return $this->belongsTo(CampaignProduct::class);
    }

    /**
     * Группа
     *
     * @return BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Ключевик
     *
     * @return BelongsTo
     */
    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }

    /**
     * Статус
     *
     * @return BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Статистика
     *
     * @return HasMany
     */
    public function statistics()
    {
        return $this->hasMany(CampaignKeywordStatistic::class);
    }

    /**
     * Получить id ключевых слов, прикреплённых к товару или группе рекламной кампании, в которых присутствует стоп слово
     *
     * @param  array  $stopWordRequest
     * @param  Campaign  $campaign
     * @return array
     */
    public static function getAttachKeywordIdsFromStopWord(array $stopWordRequest, Campaign $campaign): array
    {
        $keywordsQuery = CampaignKeyword::leftJoin('keywords as k', 'campaign_keywords.keyword_id', '=', 'k.id')
            ->where('cg.campaign_id', '=', $campaign->id)
            ->whereNotIn('campaign_keywords.status_id', [Status::DELETED, Status::ARCHIVED])
            ->where(function ($query) use ($stopWordRequest) {
                $query->where('k.name', 'like', '% ' . $stopWordRequest['stop_word_name'] . ' %')
                    ->orWhere('k.name', 'like', $stopWordRequest['stop_word_name'] . ' %')
                    ->orWhere('k.name', 'like', '% ' . $stopWordRequest['stop_word_name']);
            })->select('campaign_keywords.keyword_id');

        if (!empty($stopWordRequest['campaign_product_id'])) {
            $keywordsQuery->where('campaign_keywords.campaign_product_id', '=', $stopWordRequest['campaign_product_id'])
                ->leftJoin('campaign_products AS cg', 'campaign_keywords.campaign_product_id', '=', 'cg.id');

            return $keywordsQuery->pluck('campaign_keywords.keyword_id')->toArray();
        }

        if (!empty($stopWordRequest['group_id'])) {
            $keywordsQuery->where('campaign_keywords.group_id', '=', $stopWordRequest['group_id'])
                ->leftJoin('campaign_products AS cg', 'campaign_stop_words.group_id', '=', 'cg.group_id');

            return $keywordsQuery->pluck('campaign_keywords.keyword_id')->toArray();
        }

        return [];
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public static function getKeywordIdsBasedAttachStopWord(Request $request): array
    {
        $groupId = $request->get('group_id') ?? null;
        $productId = $request->get('campaign_product_id') ?? null;

        if (empty($groupId) && empty($productId)) {
            return [];
        }

        $relatedStopWordQuery = CampaignStopWord::select([DB::raw('DISTINCT stop_word_id AS id'), 'sw.name'])
            ->leftJoin('stop_words as sw', 'campaign_stop_words.stop_word_id', '=', 'sw.id');

        if (!empty($groupId)) {
            $relatedStopWordQuery->where('campaign_stop_words.group_id', '=', $groupId)
                ->leftJoin('campaign_products AS cg', 'campaign_stop_words.group_id', '=', 'cg.group_id');
        }

        if (!empty($productId)) {
            $relatedStopWordQuery->where('campaign_stop_words.campaign_product_id', '=', $productId)
                ->leftJoin('campaign_products AS cg', 'campaign_stop_words.campaign_product_id', '=', 'cg.id');
        }

        $relatedStopWordNames = $relatedStopWordQuery->pluck('name')->toArray();

        if (!empty($relatedStopWordNames)) {
            $keywordsQuery = Keyword::select(DB::raw('DISTINCT id'));

            foreach ($relatedStopWordNames as $stopWordName) {
                $keywordsQuery->orWhere([
                    ['name', 'like', '% ' . $stopWordName . ' %', 'or'],
                    ['name', 'like', $stopWordName . ' %', 'or'],
                    ['name', 'like', '% ' . $stopWordName, 'or']
                ]);
            }

            return $keywordsQuery->pluck('id')->toArray();
        }

        return [];
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public static function getAttachKeywordIds(Request $request): array
    {
        $groupId = $request->get('group_id') ?? null;
        $productId = $request->get('campaign_product_id') ?? null;

        if (empty($groupId) && empty($productId)) {
            return [];
        }

        $relatedKeywordIds = CampaignKeyword::select([DB::raw('DISTINCT keyword_id AS id')])
            ->join('campaign_products AS cg', function ($join) {
                $join->on('campaign_keywords.campaign_product_id', '=', 'cg.id');
                $join->orOn('campaign_keywords.group_id', '=', 'cg.group_id');
            });

        if (!empty($groupId)) {
            $relatedKeywordIds->where('campaign_keywords.group_id', '=', $groupId);
        }

        if (!empty($productId)) {
            $relatedKeywordIds->where('campaign_keywords.campaign_product_id', '=', $productId);
        }

        return $relatedKeywordIds->pluck('id')->toArray();
    }

    /**
     * Save campaign keywords
     *
     * @param  array  $request
     * @return array not save keywords
     */
    public static function saveKeywords(array $request): array
    {
        $missedKeywords = [];

        foreach ($request as $keywordData) {
            if (array_key_exists('keyword_name', $keywordData)) {
                $keyword = Keyword::saveKeyword($keywordData);
                $keywordData['keyword_id'] = optional($keyword)->id;
            }

            if (empty($keywordData['keyword_id'])) {
                $result = false;
            } else {
                $campaignKeyword = self::findOrCreateModel($keywordData);
                $campaignKeyword->fill($keywordData);
                $campaignKeyword->bid = $keywordData['bid'] ?? self::DEFAULT_KEYWORD_BID;
                $campaignKeyword->status_id = Status::ACTIVE;
                $result = $campaignKeyword->save();
            }

            if (!$result) {
                $missedKeyword = [
                    'keyword_id' => $keywordData['keyword_id'],
                    'product_id' => $keywordData['campaign_product_id'] ?? null,
                    'group_id' => $keywordData['group_id'] ?? null
                ];
                $missedKeywords[] = $missedKeyword;
            }
        }

        return $missedKeywords;
    }

    /**
     * Search or create campaign keyword
     *
     * @param  array  $request
     * @return CampaignKeyword
     */
    public static function findOrCreateModel(array $request): CampaignKeyword
    {
        if (!array_key_exists('keyword_id', $request) && !array_key_exists('campaign_product_id', $request)
            && !array_key_exists('group_id', $request)) {
            return new CampaignKeyword();
        }

        $model = self::findModel($request);

        if (empty($model)) {
            return new CampaignKeyword();
        }

        return $model;
    }

    /**
     * Find campaign keyword
     *
     * @param  array  $request
     * @return mixed
     */
    public static function findModel(array $request)
    {
        if (!array_key_exists('keyword_id', $request) && !array_key_exists('campaign_product_id', $request)
            && !array_key_exists('group_id', $request)) {
            return null;
        }

        return CampaignKeyword::where('keyword_id', '=', $request['keyword_id'])
            ->where(function (Builder $query) use ($request) {
                $query->where('campaign_product_id', '=', $request['campaign_product_id'] ?? null)
                    ->orWhere('group_id', '=', $request['group_id'] ?? null);
            })->first();
    }

    /**
     * Detach keywords
     *
     * @param  array  $request
     * @param  Campaign  $campaign
     * @return mixed
     */
    public static function detachKeywords(array $request, Campaign $campaign)
    {
        $productId = $request['campaign_product_id'] ?? null;
        $groupId = $request['group_id'] ?? null;
        $keywords = $request['keyword_ids'];
        $campaignKeywords = CampaignKeyword::whereIn('campaign_keywords.keyword_id', $keywords)
            ->whereNotIn('campaign_keywords.status_id', [Status::ARCHIVED, Status::DELETED])
            ->where('cg.campaign_id', '=', $campaign->id)
            ->leftJoin('campaign_products AS cg', 'campaign_keywords.campaign_product_id', '=', 'cg.id');

        if (!empty($productId)) {
            $campaignKeywords->where('campaign_keywords.campaign_product_id', '=', $productId);
        }

        if (!empty($groupId)) {
            $campaignKeywords->where('campaign_keywords.group_id', '=', $groupId);
        }

        $count = $campaignKeywords->update([
            'campaign_keywords.bid' => 0,
            'campaign_keywords.status_id' => Status::DELETED,
        ]);

        return $count;
    }
}
