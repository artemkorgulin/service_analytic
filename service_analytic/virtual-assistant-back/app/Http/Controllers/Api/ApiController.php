<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OzDataCategory;
use App\Models\RootQuery;
use App\Models\SearchQueryHistory;
use App\Services\ParsingFilesService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;

class ApiController extends Controller
{
    private function setRequest(Request $request){
        $this->user = $request->get('user');
        $this->page = abs((int)$request->page) ?: 1;
        if (app()->runningInConsole() === false && $this->user) {
            if ($request->perPage) {
                $this->pagination = (int)$request->perPage;
                Cache::put($this->user['id'] . $this->paginationPostfix, $this->pagination);
            } elseif (Cache::has($this->user['id'] . $this->paginationPostfix)) {
                $this->pagination = Cache::get($this->user['id'] . $this->paginationPostfix);
            }
        }
    }

    /**
     * Получить популярность ключевого слова
     *
     * @param Request $request
     * @return mixed
     */
    public function getKeywordsPopularity(Request $request)
    {
        $this->setRequest($request);
        $request->validate(
            [
                'dateFrom' => 'required|string',
                'dateTo' => 'required|string',
                'keywords' => 'required|array',
                'keywords.*' => 'array',
                'keywords.*.id' => 'integer',
                'keywords.*.name' => 'string',
                'keywords.*.category_id' => 'integer',
            ]
        );

        $keywords = $request->get('keywords');
        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');

        $popularities = [];

        foreach ($keywords as $keyword) {
            $keyword = (object)$keyword;

            $ozonCategory = OzDataCategory::query()
                ->join('oz_data_seller_categories', 'oz_data_seller_categories.ozon_category_id', '=', 'oz_data_categories.id')
                ->where('ozon_id', $keyword->category_id)
                ->select('oz_data_categories.id', 'oz_data_categories.name')
                ->first();

            $popularity = SearchQueryHistory::query()
                ->whereHas('searchQuery', function (Builder $query) use ($keyword) {
                    //$regexp = '^(.+\s+|)'.$keyword.'(|\s+.+)$';
                    //$query->where('name', 'regexp', $regexp);
                    $query->where('name', $keyword->name);
                })
                ->whereDate('parsing_date', '>=', Carbon::parse($dateFrom))
                ->whereDate('parsing_date', '<=', Carbon::parse($dateTo))
                ->where(function (Builder $query) use ($ozonCategory) {
                    $query->where('search_query_histories.ozon_category_id', $ozonCategory->id)
                        ->orWhereNull('search_query_histories.ozon_category_id');
                })
                ->groupBy('parsing_date')
                ->selectRaw('DATE(parsing_date) as date')
                ->selectRaw('SUM(popularity) as popularity')
                ->pluck('popularity', 'date');

            // Если мы не нашли записей о популярности
            if ($popularity->count() == 0) {
                // проверяем, существует ли такой корневой запрос
                $rootQuery = RootQuery::query()
                    ->where('name', $keyword->name)
                    ->where('ozon_category_id', $ozonCategory->id)
                    ->exists();

                if (!$rootQuery) {
                    $res = $this->addKeywordToRootQueriesForParsing($keyword->name, $ozonCategory->name);
                    if ($res) {
                        RootQuery::create([
                            'name' => $keyword->name,
                            'ozon_category_id' => $ozonCategory->id
                        ]);
                    }
                }
            }

            $popularities[$keyword->name] = [
                'keywordId' => $keyword->id,
                'popularities' => $popularity,
            ];
        }

        return response()->api(
            true,
            $popularities,
            []
        );
    }

    /**
     * Добавить запись в файл для парсинга
     *
     * @param string $keywordName
     * @param string $ozonCategoryName
     *
     * @return bool
     */
    protected function addKeywordToRootQueriesForParsing(Request $request, $keywordName, $ozonCategoryName)
    {
        $this->setRequest($request);
        $errorsBag = new MessageBag();
        $filePath = config('sources.root_queries_file');
        $f = fopen(base_path(config('sources.parser_local_path') . $filePath), 'a') or new Exception(__('import.error_opening_file'));

        $row = [
            $ozonCategoryName,
            $keywordName,
            '',
        ];
        fputcsv($f, $row, ';');
        fclose($f);

        $parsingFilesService = new ParsingFilesService(ParsingFilesService::DIRECTION_OUT);
        $res = $parsingFilesService->loadToFtp($filePath);

        if (!$res) {
            $errors = $parsingFilesService->getErrors();
            $errorsBag->merge($errors);
        }

        return $res;
    }

    /**
     * Получить популярность ключевого слова
     *
     * @param Request $request
     * @return mixed
     */
    public function findKeywordsWithStatistics(Request $request)
    {
        $this->setRequest($request);
        $request->validate(
            [
                'dateFrom' => 'required|string',
                'dateTo' => 'required|string',
                'keyword' => 'required|string',
                'categoryId' => 'integer',
            ]
        );

        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');
        $keyword = $request->get('keyword');
        $categoryId = $request->get('categoryId');

        $regexp = '^(.+\s+|)' . $keyword . '(|\s+.+)$';

        $statQuery = SearchQueryHistory::query()
            ->join('search_queries', 'search_queries.id', '=', 'search_query_histories.search_query_id')
            ->leftJoin('oz_data_seller_categories', 'oz_data_seller_categories.ozon_category_id', '=', 'search_query_histories.ozon_category_id')
            ->where('search_queries.name', 'regexp', $regexp)
            //->where('search_queries.name', 'like', $keyword)
            ->where('search_queries.is_negative', false)
            ->whereDate('parsing_date', '>=', Carbon::parse($dateFrom))
            ->whereDate('parsing_date', '<=', Carbon::parse($dateTo))
            ->where(function (Builder $query) use ($categoryId) {
                $query->when($categoryId, function (Builder $query) use ($categoryId) {
                    $query->where('ozon_id', $categoryId);
                })
                    ->orWhereNull('search_query_histories.ozon_category_id');
            })
            ->orderBy('popularity', 'desc')
            ->select('search_queries.id', 'search_queries.name')
            ->selectRaw('DATE(parsing_date) as date')
            ->addSelect('ozon_id as category_id', 'popularity', 'additions_to_cart', 'avg_price');

        $statistics = SearchQueryHistory::query()
            ->fromSub($statQuery, 'statistics')
            ->groupBy('name', 'date', 'category_id')
            ->orderBy('name', 'asc')
            ->orderBy('date', 'asc')
            ->orderBy('category_id', 'desc')
            ->get();

        return response()->api(
            true,
            $statistics,
            []
        );
    }
}
