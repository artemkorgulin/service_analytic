<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\ImportController;
use App\Services\SearchQueryLoader;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchQueriesController extends ImportController
{
    /**
     * Загрузка файла с поисковыми запросами
     *
     * @param Request $request
     * @param SearchQueryLoader $loader
     * @return RedirectResponse|View
     */
    public function loadSearchQueries(Request $request, SearchQueryLoader $loader)
    {
        // Проверем наличие URL
        $request->validate(
            [
                'parserOutCsvUrl' => 'required|string'
            ]
        );

        [$count, $duration] = $this->process($request, $loader);

        if ($this->errors->any()) {
            return view('import.seacrh_queries')->withErrors($this->errors->all());
        }

        return view('import.seacrh_queries')
            ->with('message', __('import.file_has_been_processed', [
                'datetime' => Carbon::now()->toDateTimeString(),
                'count' => $count,
                'min' => $duration->i,
                'sec' => $duration->s,
            ]));
    }
}
