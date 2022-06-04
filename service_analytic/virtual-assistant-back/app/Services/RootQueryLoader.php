<?php

namespace App\Services;

use App\Models\OzAlias;
use App\Contracts\LoaderInterface;
use App\Models\RootQuery;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class RootQueryLoader
 *
 * Загрузчик корневых запросов
 *
 * @package App\Services
 */
class RootQueryLoader extends QueryService implements LoaderInterface
{
    protected $savedAliases = [];

    /**
     * Загрузить запросы по url
     *
     * @param string $filePath Путь к файлу
     * @param bool $processTitles Загружать заголовки?
     *
     * @return int
     */
    public function load(string $filePath, bool $processTitles = true): int
    {
        $start = Carbon::now();

        // Собираем массив корневых запросов из файла
        $rootQueries = $this->parseFile($filePath);

        if (!empty($rootQueries)) {
            // Загружаем список категорий Озон
            $this->loadOzonCategories();

            // Содержит ли первая строка заголовки
            if ($processTitles) {
                array_shift($rootQueries);
            }

            foreach ($rootQueries as $arRootQuery) {
                // Сохраняем корневой запрос
                $this->saveRootQuery($arRootQuery);

                // Разбиваем на слова
                $rootQueryWords = explode(' ', $arRootQuery['root_query']);

                // Если слов больше, чем 1
                if (count($rootQueryWords) > 1) {
                    // Комбинируем
                    $newRootQueries = (array)$this->combine($rootQueryWords);

                    foreach ($newRootQueries as $newRootQueryName) {
                        // Сохраняем уникальные получившиеся корневые запросы
                        if ($newRootQueryName != $arRootQuery['root_query']) {
                            $arRootQuery['root_query'] = $newRootQueryName;
                            $this->saveRootQuery($arRootQuery);
                        }
                    }
                }
            }
        }

        $this->duration = $start->diff(Carbon::now());

        return count($rootQueries);
    }

    /**
     * Парсинг csv файла
     *
     * @param string $filePath
     *
     * @return array
     */
    protected function parseFile($filePath)
    {
        $rootQueries = [];

        try {
            $f = fopen(storage_path('app/' . $filePath), "rt") or new Exception(__('import.error_opening_file'));
        } catch (Exception $exception) {
            report($exception);
            Log::info('Ошибка при чтении файла, ошибка ' . $filePath . ', ошибка: ' . $exception->getMessage());
            return $rootQueries;
        }

        for ($i = 0; ($data = fgetcsv($f, 1000, ";")) !== false; $i++) {
            $rootQueries[] = [
                'ozon_category_name' => Str::lower(trim($data[0])),
                'root_query' => Str::lower(trim($data[1])),
                'oz_aliases' => $data[2] ? explode(';', Str::lower($data[2])) : null
            ];
        }

        return $rootQueries;
    }

    /**
     * Сохранить корневой запрос
     *
     * @param array $arRootQuery
     *
     * @return bool
     */
    protected function saveRootQuery($arRootQuery)
    {
        $ozonCategoryName = $arRootQuery['ozon_category_name'];
        $name = $arRootQuery['root_query'];

        if (empty($name)) {
            return false;
        }

        // Находим соответствие категории Озон
        $ozonCategoryId = $this->getOzonCategoryIdByName($ozonCategoryName);
        if (!$ozonCategoryId) {
            $this->errors->add(
                'category_not_found',
                __('import.category_not_found', ['category' => $ozonCategoryName, 'query' => $name])
            );
            return false;
        }

        // Ищем модель
        $rootQuery = RootQuery::query()
            ->where('name', $name)
            ->where('ozon_category_id', $ozonCategoryId)
            ->with('oz_aliases')
            ->first();

        if (!$rootQuery) {
            dump($ozonCategoryId . ' ' . $name);
            // Создаем новый корневой запрос
            $rootQuery = new RootQuery();
            $rootQuery->ozon_category_id = $ozonCategoryId;
            $rootQuery->name = $name;
        }

        $rootQuery->is_visible = true;
        $res = $rootQuery->save();
        if (!$res) {
            $this->errors->add(
                'root_query_save_error',
                __('import.root_query_save_error', ['query' => $name])
            );
            return false;
        }

        // Синонимы
        $this->saveAliases($arRootQuery['oz_aliases'], $rootQuery);

        return true;
    }

    /**
     * Скомбинировать слова
     *
     * @param $words
     *
     * @return string|array
     */
    protected function combine($words)
    {
        if (count($words) == 2) {
            return $words[1] . ' ' . $words[0];
        }
        if (count($words) == 3) {
            var_dump($words);
            $phrases = [];
            for ($i = 0; $i < count($words); $i++) {
                $phrases[] = $words[$i] . ' ' . $this->combine([$words[($i + 1) % 3], $words[($i + 2) % 3]]);
                $phrases[] = $words[$i] . ' ' . $this->combine([$words[($i + 2) % 3], $words[($i + 1) % 3]]);
            }
            return $phrases;
        }

        return implode(' ', $words);
    }

    /**
     * Создать модели для синонимов по списку
     *
     * @param string[] $aliasesTitles
     *
     * @param RootQuery $rootQuery
     */
    protected function saveAliases($aliasesTitles, $rootQuery)
    {
        if (is_array($aliasesTitles)) {
            foreach ($aliasesTitles as $aliasTitle) {
                if (!$aliasTitle) {
                    continue;
                }

                $alias = $this->saveAlias($aliasTitle, $rootQuery);
                if ($alias) {
                    // Разбиваем на слова
                    $aliasWords = explode(' ', $aliasTitle);

                    // Если слов > 1
                    if (count($aliasWords) > 1) {
                        // Комбинируем
                        $combinedAliases = (array)$this->combine($aliasWords);

                        foreach ($combinedAliases as $combinedAliasTitle) {
                            // Сохраняем все уникальные
                            if ($combinedAliasTitle != $aliasTitle) {
                                $this->saveAlias($combinedAliasTitle, $rootQuery);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Создать объект синонима
     *
     * @param string $aliasTitle
     * @param RootQuery $rootQuery
     *
     * @return OzAlias|bool
     */
    protected function saveAlias($aliasTitle, $rootQuery)
    {
        // Ключ синонима
        $key = $rootQuery->id . '.' . $aliasTitle;

        // Проверяем, что есть, что сохранять, и что такого синонима еще не было
        if (!$aliasTitle || array_key_exists($key, $this->savedAliases)) {
            return false;
        }

        // Ищем модель
        $alias = $rootQuery->aliases()
            ->where('name', $aliasTitle)
            ->first();

        if (!$alias) {
            dump($rootQuery->ozon_category_id . ' ' . $rootQuery->name . ' ' . $key);
            // Новый экземпляр
            $alias = new OzAlias();
            $alias->name = trim($aliasTitle);
            $alias->root_query_id = $rootQuery->id;

            // Сохраняем
            $res = $alias->save();
            if (!$res) {
                $message = __('import.alias_save_error', ['alias' => $aliasTitle, 'query' => $rootQuery->name]);
                $this->errors->add('alias_save_error', $message);
                return false;
            }
        }

        $this->savedAliases[$key] = $alias->id;

        return $alias;
    }
}
