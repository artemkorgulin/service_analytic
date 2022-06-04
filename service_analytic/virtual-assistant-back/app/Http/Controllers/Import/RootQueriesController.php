<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\ImportController;
use App\Services\RootQueryLoader;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RootQueriesController extends ImportController
{
    protected $clear = [];

    /**
     * Загрузка файла с корневыми запросами
     *
     * @param Request $request
     * @param RootQueryLoader $loader
     * @return RedirectResponse|View
     */
    public function loadRootQueries(Request $request, RootQueryLoader $loader)
    {
        [$count, $duration] = $this->process($request, $loader);

        if ($this->errors->any()) {
            return view('import.root_queries')->withErrors($this->errors->all());
        }

        return view('import.root_queries')
            ->with('message', __('import.file_has_been_processed', [
                'datetime' => Carbon::now()->toDateTimeString(),
                'count' => $count,
                'hours' => $duration->h,
                'min' => $duration->i,
                'sec' => $duration->s,
            ]));
    }

    /**
     * Сохранение файла в storage
     *
     * @param Request $request
     * @param string $fieldName
     * @return false|string
     */
    protected function saveFile(Request $request, $fieldName)
    {
        // Сохраняем файл для парсера
        $request->file($fieldName)->storePubliclyAs('parser', 'ozon_seller_analytic_query.csv');

        return parent::saveFile($request, $fieldName);
    }
}
