<?php

namespace App\Services;

use App\Contracts\LoaderInterface;
use App\Models\NegativeKeyword;
use App\Models\SearchQuery;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class NegativeKeywordLoader
 *
 * Загрузчик минус-слов
 *
 * @package App\Services
 */
class NegativeKeywordLoader extends QueryService implements LoaderInterface
{
    /**
     * Загрузить запросы по url
     *
     * @param string $filePath Путь к файлу
     * @param bool $processTitles Загружать заголовки?
     * @return int
     */
    public function load($filePath, $processTitles = true): int
    {
        // Собираем массив корневых запросов из файла
        $this->parseFile($filePath);

        // Содержит ли первая строка заголовки
        if ($processTitles) {
            array_shift($this->negativeKeywordsList);
        }

        if (!empty($this->negativeKeywordsList)) {
            foreach ($this->negativeKeywordsList as $arNegativeKeyword) {
                NegativeKeyword::firstOrCreate(['name' => $arNegativeKeyword]);
            }

            var_dump("Проверяем все загруженные поисковые запросы на соответствие новым минус-словам");

            $k = 0;
            // Проверяем все загруженные поисковые запросы на соответствие новым минус-словам
            SearchQuery::query()
                ->where('is_negative', false)
                ->chunk(10000, function (Collection $searchQueries) use (&$k) {
                    foreach ($searchQueries as $searchQuery) {
                        $searchQuery->is_negative = $this->hasNegative($searchQuery);
                        if ($searchQuery->is_negative) {
                            $searchQuery->save();
                        }
                    }
                    $k += count($searchQueries);
                    var_dump($k);
                });
        }

        return count($this->negativeKeywordsList);
    }

    /**
     * Парсинг csv файла
     *
     * @param string $filePath
     * @return array
     */
    protected function parseFile($filePath)
    {
        $this->negativeKeywordsList = [];

        try {
            $f = fopen(storage_path('app/' . $filePath), "rt") or new Exception(__('error_opening_file'));
        } catch (Exception $exception) {
            report($exception);
            Log::error('Ошибка при чтении файла, ошибка ' . $filePath . ', ошибка: ' . $exception->getMessage());
            return $this->negativeKeywordsList;
        }

        for ($i = 0; ($data = fgetcsv($f, 1000, ";")) !== false; $i++) {
            $this->negativeKeywordsList[] = Str::lower(trim($data[0]));
        }

        return $this->negativeKeywordsList;
    }
}
