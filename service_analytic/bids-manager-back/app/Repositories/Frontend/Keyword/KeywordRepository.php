<?php

namespace App\Repositories\Frontend\Keyword;

use App\Http\Requests\V2\Keyword\KeywordGetListByFilterRequest;
use App\Models\CampaignKeyword;
use App\Models\Keyword as Model;
use App\Repositories\BaseRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class KeywordRepository
 *
 * @package App\Repositories\Frontend\Keyword
 */
class KeywordRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    /**
     * @param  KeywordGetListByFilterRequest  $request
     * @return LengthAwarePaginator
     */
    public function getKeywordsSearch(KeywordGetListByFilterRequest $request)
    {
        $attachKeywordIds = CampaignKeyword::getAttachKeywordIds($request);
        $keywordIdsBasedAttachStopWord = CampaignKeyword::getKeywordIdsBasedAttachStopWord($request);
        $excludeIds = array_merge($attachKeywordIds, $keywordIdsBasedAttachStopWord);
        $result = $this->startConditions()
            ->select([
                DB::raw('DISTINCT keywords.id'),
                'keywords.name',
                DB::raw('MAX(popularity) OVER(PARTITION BY kp.keyword_id ORDER BY kp.`popularity` desc) AS popularity')
            ])
            ->leftJoin('keyword_popularities AS kp', 'keywords.id', '=', 'kp.keyword_id')
            ->whereNotIn('keyword_id', $excludeIds)
            ->whereNotNull('kp.popularity')
            ->orderByDesc('kp.popularity');

        if (!empty($request['search'])) {
            $result->where('name', 'like', '%' . $request['search'] .'%');
        }

        return PaginatorHelper::addPagination($request, $result);
    }

    /**
     * @param string $name
     * @return Model|null
     */
    public function getByName(string $name)
    {
        return $this->startConditions()
            ->where('name', $name)
            ->first();
    }
}
