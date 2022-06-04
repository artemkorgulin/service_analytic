<?php

namespace App\Services;

use App\Contracts\LoaderInterface;
use App\Models\NegativeKeyword;
use App\Repositories\RootQueryRepository;
use App\Repositories\SearchQueryRepository;
use App\Models\RootQuerySearchQuery;
use App\Models\SearchQuery;
use App\Models\SearchQueryHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class SearchQueryLoader
 *
 * Загрузчик поисковых запросов
 *
 * @package App\Services
 */
class SearchQueryLoader extends QueryService implements LoaderInterface
{
    const TIME_LAG = 3;

    protected $rootQueriesList = [];
    protected $negativeKeywordsList = [];
    protected $savedSearchQueries = [];

    /**
     * Загрузить запросы по url
     *
     * @param  string  $filePath  URL к файлу
     * @param  bool  $processTitles  Загружать заголовки?
     * @param  bool  $calcTotals  Считать итоговые показатели?
     *
     * @return int|boolean
     */
    public function load(string $filePath, bool $processTitles = true, $calcTotals = true): int
    {
        $start = Carbon::now();

        // Загружаем файл с FTP парсинга
        $parsingFileService = new ParsingFilesService;
        $localFile = $parsingFileService->loadFromFtp($filePath);
        if (!$localFile) {
            $errors = $parsingFileService->getErrors();
            foreach ($errors as $key => $messages) {
                foreach ($messages as $message) {
                    echo $message."\r\n";
                }
            }
            return false;
        }

        // Очищаем массивы
        $this->savedSearchQueries = [];

        // Загружаем список категорий Озон
        $this->loadOzonCategories();

        // Загружаем список минус-слов
        $this->loadNegativeKeywords();

        // Обрабатываем файл
        $count = $this->parseFile($localFile, $processTitles);

        // Обновляем показатели поисковых и корневых запросов
        if ($count && $calcTotals) {
            echo date('H:i:s').': Updating root queries search queries links'."\r\n";
            $this->updateSearchQueries();
        }

        $this->duration = $start->diff(Carbon::now());

        return $count;
    }

    /**
     * Обновить показатели поисковых запросов
     *
     * @return bool
     */
    public function calcSearchQueriesParams(): bool
    {
        $start = Carbon::now();

        // Обновляем показатели поисковых и корневых запросов
        echo date('H:i:s').': Updating root queries search queries links'."\r\n";
        $this->updateSearchQueries();

        $this->duration = $start->diff(Carbon::now());

        return true;
    }

    /**
     * Загрузить запросы по url
     *
     * @param  string  $filePath  URL к файлу
     * @param  Carbon  $date  Дата загрузки
     * @param  bool  $processTitles  Загружать заголовки?
     * @param  bool  $calcTotals  Считать итоговые показатели?
     *
     * @return int|boolean
     */
    public function loadWeekly(string $filePath, Carbon $date, bool $processTitles = true, $calcTotals = true): int
    {
        $start = Carbon::now();

        // Загружаем файл с FTP парсинга
        $parsingFileService = new ParsingFilesService;
        $localFile = $parsingFileService->loadFromFtp($filePath);

        // Проверка, загрузили ли файл
        if (!$localFile) {
            $errors = $parsingFileService->getErrors();

            // Формируем файл-ответку, что есть ошибка
            $errorFilePath = config('sources.search_queries_weekly_error_file');
            $path = base_path(config('sources.parser_local_path').$errorFilePath);

            if (!file_exists($path)) {
                touch($path);
            }

            if (file_exists($path)) {
                $errorFile = fopen($path, 'a')
                or new Exception(__('import.error_opening_file'));

                foreach ($errors as $key => $messages) {
                    foreach ($messages as $message) {
                        echo $message."\r\n";
                        fputcsv(
                            $errorFile,
                            [
                                Carbon::now()->toDateTimeString(),
                                $key,
                                $message
                            ],
                            ';'
                        );
                    }
                }

                fclose($errorFile);
            }

            $parsingFileService->setDirection(ParsingFilesService::DIRECTION_OUT);
            $parsingFileService->loadToFtp($errorFilePath);

            return false;
        }

        // Загружаем список категорий Озон
        $this->loadOzonCategories();

        // Обрабатываем файл
        $count = $this->parseWeeklyFile($localFile, $processTitles);

        // Обновляем показатели поисковых и корневых запросов
        if ($calcTotals) {
            echo date('H:i:s').': Updating root queries search queries links'."\r\n";
            $this->updateSearchQueriesWeekly($date);
        }

        $this->duration = $start->diff(Carbon::now());

        return $count;
    }

    /**
     * Скачать файл по URL
     *
     * @param  string  $url
     *
     * @return bool|string
     */
    protected function downloadFileByUrl($url)
    {
        $content = file_get_contents($url);

        $filePath = 'import/'.date('Y-m-d').'/'.basename($url);

        if (Storage::disk('local')->put($filePath, $content)) {
            return $filePath;
        }

        return false;
    }

    /**
     * Парсинг csv файла
     *
     * @param  string  $filePath
     * @param  bool  $processTitles
     * @return integer
     */
    protected function parseFile(string $filePath, $processTitles = true): int
    {
        $count = 0;

        try {
            $f = fopen($filePath, 'rt') or new Exception(__('import.error_opening_file'));
        } catch (Exception $exception) {
            Log::info('Ошибка при чтении файла, ошибка '.$filePath.', ошибка: '.$exception->getMessage());
            return $count;
        }

        for ($i = 0; ($row = fgetcsv($f, 1000, ';')) !== false; $i++) {
            // Если файл содержит заголовки
            if ($processTitles && $i == 0) {
                continue;
            }

            // Если мало данных в строке
            if (count($row) < 9 || $row[2] == '' || $row[2] == '0') {
                if (count($row) < 9 || $row[2] == '') {
                    echo 'Missing line '.($i + 1)."\r\n";
                    var_dump($row);
                }
                continue;
            }

            // Массив данных
            $arSearchQuery = [
                'ozon_category_name' => Str::lower(trim($row[0])),
                'root_query' => Str::lower(trim($row[1])),
                'search_query' => Str::limit(Str::lower(trim($row[2])), 250),
                'popularity' => (int) $row[3],
                'additions_to_cart' => (int) $row[4],
                'conversion' => $row[5],
                'avg_price' => $row[6],
                'date' => $row[7],
            ];

            // Сохраняем в историю
            $this->saveSearchQueryHistory($arSearchQuery);

            $count++;
        }

        return $count;
    }

    /**
     * Парсинг еженедельного csv файла
     *
     * @param  string  $filePath
     * @param  bool  $processTitles
     * @return integer
     */
    protected function parseWeeklyFile(string $filePath, bool $processTitles = true): int
    {
        $count = 0;

        try {
            $f = fopen($filePath, 'rt') or new Exception(__('import.error_opening_file'));
        } catch (Exception $exception) {
            Log::info('Ошибка при чтении файла, ошибка '.$filePath.', ошибка: '.$exception->getMessage());
            return $count;
        }

        for ($i = 0; ($row = fgetcsv($f, 0, ';')) !== false; $i++) {
            // Если файл содержит заголовки
            if ($processTitles && $i == 0) {
                continue;
            }

            // Если мало данных в строке
            if (count($row) < 5 || $row[1] == '' || $row[1] == '0') {
                echo 'Missing line '.($i + 1)."\r\n";
                var_dump($row);
                continue;
            }

            // Массив данных
            $arSearchQuery = [
                'ozon_category_name' => Str::lower(trim($row[0])),
                'search_query' => Str::limit(Str::lower(trim($row[1])), 250),
                'products_count' => (int) $row[2],
                'products_links' => explode(',', $row[3]),
                'date' => $row[4],
            ];

            // Обновляем поисковый запрос
            $this->updateSearchQueryHistory($arSearchQuery);

            $count++;
        }

        return $count;
    }

    /**
     * Загрузить список минус-слов
     */
    protected function loadNegativeKeywords()
    {
        $this->negativeKeywordsList = NegativeKeyword::all()->pluck('name', 'id')->toArray();
    }

    /**
     * Сохранить поисковый запрос
     *
     * @param  array  $arSearchQuery
     *
     * @return bool
     */
    protected function saveSearchQueryHistory($arSearchQuery)
    {
        $ozonCategoryName = $arSearchQuery['ozon_category_name'];
        $rootQueryTitle = $arSearchQuery['root_query'];
        $searchQueryTitle = $arSearchQuery['search_query'];

        // Если поисковый запрос пустой - пропускаем его
        if (!$searchQueryTitle) {
            return false;
        }

        // Если мы такой уже обрабатывали - не обрабатываем повторно
        $key = $ozonCategoryName.'.'.$rootQueryTitle.'.'.$searchQueryTitle;
        if (isset($this->savedSearchQueries[$key])) {
            return true;
        }

        // Находим соответствие категории Озон
        $ozonCategoryId = $this->getOzonCategoryIdByName($ozonCategoryName);
        if (is_null($ozonCategoryId)) {
            $message = __('import.category_not_found', ['category' => $ozonCategoryName, 'query' => $searchQueryTitle]);
            $this->errors->add('category_not_found', $message);
            return false;
        }

        // Ищем соответствующий корневой запрос
        // (или запросы, если категория не определена)
        $rootQueryIds = $this->findRootQueries($ozonCategoryId, $rootQueryTitle);
        if (!$rootQueryIds) {
            $message = __('import.root_query_not_found', ['category' => $ozonCategoryName, 'query' => $rootQueryTitle]);
            $this->errors->add('root_query_not_found', $message);
            return false;
        }

        // Ищем запрос в базе поисковых запросов
        $searchQuery = SearchQueryRepository::findFirstByTitleWithRootQuery($rootQueryIds, $searchQueryTitle);

        if (!$searchQuery) {
            $searchQuery = new SearchQuery();
            $searchQuery->name = $searchQueryTitle;

            // Проверяем на минус-слова
            $searchQuery->is_negative = $this->hasNegative($searchQuery);

            $res = $searchQuery->save();
            if (!$res) {
                $message = __('import.search_query_save_error', ['query' => $searchQueryTitle]);
                $this->errors->add('search_query_save_error', $message);
                return false;
            }
        }

        // Сохраняем запись в историю
        $searchQueryHistory = SearchQueryHistory::query()
            ->where('search_query_id', $searchQuery->id)
            ->where('ozon_category_id', $ozonCategoryId ?: null)
            ->where('parsing_date', Carbon::parse($arSearchQuery['date'])->toDateString())
            ->first();

        if (!$searchQueryHistory) {
            $searchQueryHistory = new SearchQueryHistory();
            $searchQueryHistory->search_query_id = $searchQuery->id;
            $searchQueryHistory->ozon_category_id = $ozonCategoryId ?: null;
            $searchQueryHistory->parsing_date = Carbon::parse($arSearchQuery['date'])->toDateString();
        }

        $searchQueryHistory->popularity = $arSearchQuery['popularity'];
        $searchQueryHistory->additions_to_cart = $arSearchQuery['additions_to_cart'];
        $searchQueryHistory->avg_price = str_replace(",", ".", $arSearchQuery['avg_price']);

        $res = $searchQueryHistory->save();

        if (!$res) {
            $message = __('import.search_query_history_save_error', ['query' => $searchQueryTitle]);
            $this->errors->add('search_query_history_save_error', $message);
            return false;
        }

        // Обновляем привязку корневых запросов
        if (!empty($rootQueryIds)) {
            $rootQueryIds = (array) $rootQueryIds;
            $searchQuery->rootQueries()->syncWithoutDetaching($rootQueryIds);
        }

        // Сохраняем в памяти
        $this->savedSearchQueries[$key] = $searchQuery->id;

        return true;
    }

    /**
     * Обновить историю поисковых запросов по итогам недели
     *
     * @param  array  $arSearchQuery
     *
     * @return bool
     */
    protected function updateSearchQueryHistory($arSearchQuery)
    {
        $ozonCategoryName = $arSearchQuery['ozon_category_name'];
        $searchQueryTitle = $arSearchQuery['search_query'];

        // Находим соответствие категории Озон
        $ozonCategoryId = $this->getOzonCategoryIdByName($ozonCategoryName);
        if (is_null($ozonCategoryId)) {
            $message = __('import.category_not_found', ['category' => $ozonCategoryName, 'query' => $searchQueryTitle]);
            $this->errors->add('category_not_found', $message);
            return false;
        }

        // Ищем запрос в базе поисковых запросов
        $searchQueries = SearchQueryRepository::findByTitleInCategory($ozonCategoryId, $searchQueryTitle);

        // Дата парсинга (минус 3 дня, так как история еще отсутствует)
        $date = Carbon::createFromFormat('Y.m.d H:i',
            $arSearchQuery['date'])->subDays(static::TIME_LAG)->toDateString();

        // Количество продуктов
        $productsCount = $arSearchQuery['products_count'];

        // Массив обновлений
        $arUpdate = [
            'products_count' => $productsCount,
            'rating' => DB::raw('CASE WHEN products_count != 0 THEN additions_to_cart * additions_to_cart / products_count ELSE 0 END')
        ];

        $searchQueriesIds = $searchQueries->modelKeys();

        // Обновляем записи в истории
        $res = SearchQueryHistory::query()
            ->when($ozonCategoryId, function (Builder $query) use ($ozonCategoryId) {
                return $query->where('ozon_category_id', $ozonCategoryId);
            }, function (Builder $query) {
                return $query->whereNull('ozon_category_id');
            })
            ->whereIn('search_query_id', $searchQueriesIds)
            ->whereDate('parsing_date', $date)
            ->update($arUpdate);

        if (!$res) {
            $message = __('import.search_query_update_error', ['queries' => implode(',', $searchQueriesIds)]);
            $this->errors->add('search_query_update_error', $message);
            return false;
        }

        return true;
    }

    /**
     * Обновить показатели поисковых запросов
     *
     * @return bool
     */
    protected function updateSearchQueries()
    {
        $limit = 10000;
        $processed = 0;

        do {
            $rootQueriesSearchQueries = RootQuerySearchQuery::query()
                ->whereHas('searchQuery', function (Builder $query) {
                    $query->where('is_negative', false);
                })
                ->with(
                    'rootQuery:id,name,ozon_category_id',
                    'searchQuery:id,name'
                )
                ->limit($limit)
                ->offset($processed)
                ->get();

            foreach ($rootQueriesSearchQueries as $rootQuerySearchQuery) {
                $rootQuery = $rootQuerySearchQuery->rootQuery;
                $searchQuery = $rootQuerySearchQuery->searchQuery;

                $history = $searchQuery->last30daysHistoriesInCategory($rootQuery->ozon_category_id)->first();

                // Обновляем показатели
                $rootQuerySearchQuery->popularity = $history->summary_popularity;
                $rootQuerySearchQuery->additions_to_cart = $history->summary_additions_to_cart;
                $rootQuerySearchQuery->avg_price = $history->avg_avg_price;

                $res = $rootQuerySearchQuery->save();
                if (!$res) {
                    $message = __('import.search_query_save_error', ['query' => $searchQuery->name]);
                    $this->errors->add('search_query_save_error', $message);
                    return false;
                }

                $processed++;
            }
        } while (count($rootQueriesSearchQueries) > 0);

        return true;
    }

    /**
     * Обновить показатели поисковых запросов по итогам еженедельного парсинга
     *
     * @param Carbon $date
     *
     * @return bool
     */
    protected function updateSearchQueriesWeekly($date)
    {
        // Дата парсинга (минус 3 дня)
        $date = $date->subDays(static::TIME_LAG)->toDateString();

        $limit = 10000;
        $processed = 0;

        do {
            $rootQueriesSearchQueries = RootQuerySearchQuery::query()
                ->join('root_queries', 'root_queries.id', '=', 'root_query_search_query.root_query_id')
                ->join('search_query_histories', function (JoinClause $join) use ($date) {
                    $join->on('search_query_histories.search_query_id', '=', 'root_query_search_query.search_query_id')
                        ->whereDate('search_query_histories.parsing_date', '=', $date)
                        ->where(function (JoinClause $query) {
                            $query->whereColumn('search_query_histories.ozon_category_id',
                                'root_queries.ozon_category_id')
                                ->orWhereNull('search_query_histories.ozon_category_id');
                        });
                })
                ->whereHas('searchQuery', function (Builder $query) {
                    $query->where('is_negative', false);
                })
                ->with('searchQuery:id,name')
                ->select(
                    'root_query_search_query.id',
                    'root_query_search_query.search_query_id'
                )
                ->selectRaw('MAX(search_query_histories.products_count) AS max_products_count')
                ->limit($limit)
                ->offset($processed)
                ->get();

            foreach ($rootQueriesSearchQueries as $rootQuerySearchQuery) {
                // Обновляем поисковые запросы
                try {
                    $rootQuerySearchQuery->products_count = $rootQuerySearchQuery->max_products_count;
                    $rootQuerySearchQuery->rating = DB::raw('CASE WHEN products_count != 0 THEN additions_to_cart * additions_to_cart / products_count ELSE 0 END');
                    $res = $rootQuerySearchQuery->save();
                } catch (\Exception $exception) {
                    report($exception);
                    $this->errors->add('search_query_save_error', $exception->getMessage());
                    return false;
                }

                if (!$res) {
                    $message = __('import.search_query_save_error',
                        ['query' => $rootQuerySearchQuery->searchQuery->name]);
                    $this->errors->add('search_query_save_error', $message);
                    return false;
                }

                $processed++;
            }
        } while (count($rootQueriesSearchQueries) > 0);

        return true;
    }

    /**
     * Найти соответствующий корневой запрос
     *
     * @param  integer  $ozonCategoryId
     * @param  string  $rootQueryTitle
     *
     * @return int|int[]|bool
     */
    protected function findRootQueries($ozonCategoryId, $rootQueryTitle)
    {
        $key = $ozonCategoryId.'.'.$rootQueryTitle;

        // Если уже находили такое
        if (isset($this->rootQueriesList[$key])) {
            return $this->rootQueriesList[$key];
        }

        // Ищем в категории
        $rootQueries = RootQueryRepository::findInCategory($ozonCategoryId, $rootQueryTitle);
        if ($rootQueries->count() != 0) {
            $rootQueryIds = $rootQueries->pluck('id')->toArray();

            // Сохраняем в памяти
            $this->rootQueriesList[$key] = $rootQueryIds;

            return $rootQueryIds;
        }

        return false;
    }
}
