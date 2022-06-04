<?php

namespace App\Services;

use App\Models\RootQuerySearchQuery;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class SearchQueryCalcService
 *
 * Расчет Коэффициентов отсеивания поисковых запросов
 *
 * @package App\Services
 */
class SearchQueryCalcService extends QueryService
{
    protected $rootQueriesCosts = [];

    /**
     * Расчитать Коэффициенты отсеивания поисковых запросов
     * и составить файл для парсинга
     *
     * @return bool
     */
    public function makeWeeklyParsingRequestFile(): bool
    {
        $start = Carbon::now();

        $limit = 10000;
        $processed = 0;

        $csvPath = config('sources.search_queries_weekly_output_file');
        $filePath = base_path(config('sources.parser_local_path') . $csvPath);

        try {
            $csv = fopen($filePath, file_exists($filePath) ? 'a' : 'w');
        } catch (Exception $e) {
            $this->errors->add(
                'import.error_opening_file',
                __('import.error_opening_file') . ' (' . $filePath . ')'
            );
            return false;
        }

        fputcsv($csv, [
            __('parsing.weekly_csv_header_category'),
            __('parsing.weekly_csv_header_root_query'),
            __('parsing.weekly_csv_header_search_query'),
        ], ';');

        do {
            $rootQueriesSearchQueries = RootQuerySearchQuery::query()
                ->whereHas('searchQuery', function (Builder $query) {
                    $query->where('is_negative', false);
                })
                ->select(
                    'id',
                    'root_query_id',
                    'search_query_id',
                    'additions_to_cart',
                    'avg_price'
                )
                ->with(
                    'rootQuery:id,name,ozon_category_id',
                    'rootQuery.ozonCategory:id,name',
                    'searchQuery:id,name'
                )
                ->orderBy('root_query_id', 'asc')
                ->limit($limit)
                ->offset($processed)
                ->get();

            foreach ($rootQueriesSearchQueries as $rootQuerySearchQuery) {
                $rootQuery = $rootQuerySearchQuery->rootQuery;
                $searchQuery = $rootQuerySearchQuery->searchQuery;

                if (isset($this->rootQueriesCosts[$rootQuery->id])) {
                    $rootQueryCost = $this->rootQueriesCosts[$rootQuery->id];
                } else {
                    $rootQueryCost = $rootQuery->searchQueriesLinks()->sum(DB::raw('additions_to_cart * avg_price'));
                    $this->rootQueriesCosts[$rootQuery->id] = $rootQueryCost;
                }

                // Расчитываем коэффициент
                $rootQuerySearchQuery->calcFilteringRatio($rootQueryCost);

                echo 'Calc ' . $searchQuery->id . ' + ' . $rootQuery->id . ', ';
                echo 'rootQueryCost = ' . $rootQueryCost . ', filtering_ratio = ' . $rootQuerySearchQuery->filtering_ratio . '' . "\r\n";

                $res = $rootQuerySearchQuery->save();
                if (!$res) {
                    $message = __('import.search_query_save_error', ['query' => $searchQuery->name]);
                    echo $message . "\r\n";
                    $this->errors->add('search_query_save_error', $message);
                    return false;
                }

                // Если он больше порогового значения
                if ($rootQuerySearchQuery->filtering_ratio >= config('coefficients.min_search_query_filtering_ratio')) {
                    // Записываем в файл
                    fputcsv($csv, [
                        $rootQuery->ozonCategory->name,
                        $rootQuery->name,
                        $searchQuery->name
                    ], ';', '"');
                }

                $processed++;
            }
        } while (count($rootQueriesSearchQueries) > 0);

        fclose($csv);

        $parsingFilesService = new ParsingFilesService(ParsingFilesService::DIRECTION_OUT);
        $res = $parsingFilesService->loadToFtp($csvPath);

        if (!$res) {
            $errors = $parsingFilesService->getErrors();
            $this->errors->merge($errors);
        }

        $this->duration = $start->diff(Carbon::now());

        return $res;
    }
}
