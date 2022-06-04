<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

/**
 * Class CampaignStopWord
 *
 * @package App\Models
 *
 * @property int $campaign_product_id
 * @property int $group_id
 * @property int $stop_word_id
 *
 * @property-read CampaignProduct $campaignProduct
 * @property-read StopWord $stopWord
 */
class CampaignStopWord extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'campaign_stop_words';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'campaign_product_id',
        'group_id',
        'stop_word_id'
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
     * Минус-слово
     *
     * @return BelongsTo
     */
    public function stopWord()
    {
        return $this->belongsTo(StopWord::class);
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
     * Save campaign stop words
     *
     * @param  array  $request
     * @param  Campaign  $campaign
     * @return array not save stop words
     */
    public static function saveStopWords(array $request, Campaign $campaign): array
    {
        $stopWordsResult = ['missed_stop_words' => [], 'detach_keywords' => []];

        foreach ($request as $stopWordData) {
            $stopWord = StopWord::saveStopWord($stopWordData);
            $stopWordData['stop_word_id'] = optional($stopWord)->id;

            if (empty($stopWordData['stop_word_id'])) {
                $result = false;
            } else {
                $campaignStopWord = self::findOrCreateModel($stopWordData);
                $campaignStopWord->fill($stopWordData);
                $result = $campaignStopWord->save();
            }

            if (!$result) {
                $stopWordsResult['missed_stop_words'][] = [
                    'stop_word_id' => $stopWordData['stop_word_id'],
                    'product_id' => $stopWordData['campaign_product_id'] ?? null,
                    'group_id' => $stopWordData['group_id'] ?? null
                ];
            }

            if (!empty($stopWordData['stop_word_name'])) {
                $detachKeywordIds = CampaignKeyword::getAttachKeywordIdsFromStopWord($stopWordData, $campaign);
                $requestKeyword = array_merge($stopWordData, ['keyword_ids' => $detachKeywordIds]);
                CampaignKeyword::detachKeywords($requestKeyword, $campaign);
                $stopWordsResult['detach_keywords'] = array_merge($stopWordsResult['detach_keywords'], $detachKeywordIds);
            }
        }

        return $stopWordsResult;
    }

    /**
     * Search or create campaign stop word
     *
     * @param  array  $request
     * @return CampaignStopWord
     */
    public static function findOrCreateModel(array $request): CampaignStopWord
    {
        if (!array_key_exists('stop_word_id', $request) && !array_key_exists('campaign_product_id', $request)
            && !array_key_exists('group_id', $request)) {

            return new CampaignStopWord();
        }

        $model = self::findModel($request);

        if (empty($model)) {
            return new CampaignStopWord();
        }

        return $model;
    }

    /**
     * Find campaign stop word
     *
     * @param  array  $request
     * @return mixed
     */
    public static function findModel(array $request)
    {
        if (!array_key_exists('stop_word_id', $request) && !array_key_exists('campaign_product_id', $request)
            && !array_key_exists('group_id', $request)) {
            return null;
        }

        return CampaignStopWord::where('stop_word_id', '=', $request['stop_word_id'])
            ->where(function (Builder $query) use ($request) {
                $query->where('campaign_product_id', '=', $request['campaign_product_id'] ?? null)
                    ->orWhere('group_id', '=', $request['group_id'] ?? null);
            })->first();
    }

    /**
     * Detach stop words
     *
     * @param  array  $request
     * @param  Campaign  $campaign
     * @return array
     */
    public static function detachStopWords(array $request, Campaign $campaign): array
    {
        $productId = $request['campaign_product_id'] ?? null;
        $groupId = $request['group_id'] ?? null;
        $campaignKeywords = CampaignStopWord::whereIn('campaign_stop_words.stop_word_id', $request['stop_word_ids'])
            ->where('cg.campaign_id', '=', $campaign->id)
            ->leftJoin('campaign_products AS cg', function ($join) {
                $join->on('campaign_stop_words.campaign_product_id', '=', 'cg.id');
                $join->orOn('campaign_stop_words.group_id', '=', 'cg.group_id');
            });

        if (!empty($productId)) {
            $campaignKeywords->where('campaign_stop_words.campaign_product_id', '=', $productId);
        }

        if (!empty($groupId)) {
            $campaignKeywords->where('campaign_stop_words.group_id', '=', $groupId);
        }

        $deleteIds = $campaignKeywords->select([DB::raw('DISTINCT campaign_stop_words.id')])->pluck('id')->toArray();
        $deleteCount = $campaignKeywords->delete();

        return ['stop_word_ids' => $deleteIds, 'deleted_count' => $deleteCount];
    }
}
