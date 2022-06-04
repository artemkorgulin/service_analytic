<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\ImportController;
use App\Services\NegativeKeywordLoader;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NegativeKeywordsController extends ImportController
{
    protected $clear = [];

    /**
     * Загрузка файла с минус-словами
     *
     * @param Request $request
     * @param NegativeKeywordLoader $loader
     * @return RedirectResponse|View
     */
    public function loadNegativeKeywords(Request $request, NegativeKeywordLoader $loader)
    {
        [$count, $duration] = $this->process($request, $loader);

        if ($this->errors->any()) {
            return view('import.negative_keywords')->withErrors($this->errors->all());
        }

        return view('import.seacrh_queries')
            ->with('message', __('import.file_has_been_processed', [
                'datetime' => Carbon::now()->toDateTimeString(),
                'count' => $count,
                'hours' => $duration->h,
                'min' => $duration->i,
                'sec' => $duration->s,
            ]));
    }
}
