<?php


namespace App\Services;

use App\Models\OzDataCategory;
use App\Models\SearchQuery;
use DateInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

class QueryService
{
    const OZON_CATEGORY_ALL = 'все';

    protected $duration;

    protected $errors;

    protected $ozonCategoriesList = [];
    protected $negativeKeywordsList = [];

    public function __construct()
    {
        set_time_limit(0);

        $this->errors = new MessageBag();
    }

    /**
     * Получить ошибки выполнения
     *
     * @return MessageBag
     */
    public function getErrors(): MessageBag
    {
        return $this->errors;
    }

    /**
     * Получить продолжительность выполнения скрипта
     *
     * @return DateInterval
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Получение категорий Ozon
     *
     * @return Collection
     */
    protected function loadOzonCategories()
    {
        $this->ozonCategoriesList = OzDataCategory::all()->pluck('id', 'name');
        return $this->ozonCategoriesList;
    }

    /**
     * Получить id категории Ozon по названию
     *
     * @param $name
     * @return int|null
     */
    protected function getOzonCategoryIdByName($name)
    {
        return $name == static::OZON_CATEGORY_ALL ? 0 : ($this->ozonCategoriesList[$name] ?? null);
    }

    /**
     * Есть ли минус-слова в запросе
     *
     * @param SearchQuery $searchQuery
     *
     * @return boolean
     */
    protected function hasNegative($searchQuery)
    {
        foreach ($this->negativeKeywordsList as $id => $negativeKeyword) {
            $regexp = '/^(.+\s+|)' . preg_quote($negativeKeyword, '/') . '(|\s+.+)$/';
            preg_match($regexp, $searchQuery->name, $matches);
            if (count($matches) > 0) {
                return true;
            }
        }
        return false;
    }
}
