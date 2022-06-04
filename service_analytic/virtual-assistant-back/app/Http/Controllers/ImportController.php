<?php

namespace App\Http\Controllers;

use App\Contracts\LoaderInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;

class ImportController extends Controller
{
    /** @var string[] Tables to clear before import */
    protected $clear = [];

    protected $errors;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->errors = new MessageBag();
    }

    /**
     * Страница импорта файла для загрузки корневых запросов
     *
     * @return View
     */
    public function rootQueriesForm()
    {
        return view('import.root_queries');
    }

    /**
     * Страница импорта файла для загрузки минус-слов
     *
     * @return View
     */
    public function negativeKeywordsForm()
    {
        return view('import.negative_keywords');
    }

    /**
     * Страница импорта файла из парсинга с поисковыми запросами
     *
     * @return View
     */
    public function searchQueriesForm()
    {
        return view('import.seacrh_queries');
    }

    /**
     * Валидация
     *
     * @param Request $request
     * @return array|void
     */
    protected function validateCsvFile(Request $request)
    {
        $request->validate(
            [
                'file' => 'required|file|mimes:csv,txt'
            ]
        );
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
        // Сохраняем для внутренней обработки
        $fileName = $request->$fieldName->getClientOriginalName();
        return $request->file($fieldName)->storeAs('import/' . date('Y-m-d'), $fileName);
    }

    /**
     * Очистить таблицы
     *
     * @param array|string $tables
     */
    protected function clearTables($tables)
    {
        if (!is_array($tables)) {
            $tables = (array)$tables;
        }

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $tableName) {
            DB::table($tableName)->truncate();
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * @param Request $request
     * @param LoaderInterface $loader
     * @return array
     */
    public function process(Request $request, LoaderInterface $loader)
    {
        $start = Carbon::now();

        // Проверяем файл
        $this->validateCsvFile($request);

        // Сохраняем в хранилище
        $filePath = $this->saveFile($request, 'file');

        // Очищаем таблицы (первичная загрузка)
        $this->clearTables($this->clear);

        // Загрузка
        $count = $loader->load($filePath, $request->input('containsTitles'));

        // Ошибки
        $this->errors->merge($loader->getErrors());

        return [$count, $start->diff(Carbon::now())];
    }
}
