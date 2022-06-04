<?php


namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Repositories\RootQuerySearchQueryRepository;
use App\Repositories\SearchQueryRepository;
use App\Models\SearchQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class ThirdStepController
 * Шаг 3 виртуального помощника
 * Работа с заголовком
 *
 * @package App\Http\Controllers\Main
 */
class ThirdStepController extends Controller
{
    /**
     * Формирование идеального заголовка товара
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $request->validate(
            [
                'ozonCategoryId' => 'required|int',
                'rootQueryTitle' => 'required|string',
                'searchQueryId' => 'required|array',
                'searchQueryId.*' => 'required|int',
                'brand' => 'required|string',
            ]
        );

        $ozonCategoryId = $request->get('ozonCategoryId');
        $rootQueryTitle = $request->get('rootQueryTitle');
        $linksIds = $request->get('searchQueryId');
        $brand = $request->get('brand');

        $limit = 10;

        $searchQueries = SearchQueryRepository::findByLinksIds($linksIds, $limit);
        $highestQualitySearchQuery = $searchQueries->first();
        $searchQueryTitle = $highestQualitySearchQuery ? $highestQualitySearchQuery->name : '';

        $characteristics = $this->detachCharacteristics($ozonCategoryId, $rootQueryTitle, $searchQueries);
        $highestQualityCharacteristics = new Collection();

        if ($searchQueryTitle && $characteristics->count() > 0) {
            $i = 0;
            while ($highestQualityCharacteristics->count() < 2 && $i < $characteristics->count()) {
                if (strpos($searchQueryTitle, $characteristics->offsetGet($i)->name) === false) {
                    $highestQualityCharacteristics->push($characteristics->offsetGet($i));
                }
                $i++;
            }
        }

        $characteristicTitles = $highestQualityCharacteristics->pluck('name')->toArray() ?? [];
        $perfectTitle = $this->makePerfectTitle($ozonCategoryId, $brand, $searchQueryTitle, $characteristicTitles);

        $response = [
            'perfectTitle' => $perfectTitle,
            'brand' => $brand,
            'searchQueries' => $searchQueries,
            'selectedSearchQueryId' => $highestQualitySearchQuery ? $highestQualitySearchQuery->id : false,
            'characteristics' => $characteristics,
            'selectedCharacteristicsIds' => $highestQualityCharacteristics->pluck('id'),
        ];

        return response()->api(
            true,
            $response,
            []
        );
    }

    /**
     * Формирование нового идеального заголовка товара
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function formNewPerfectTitle(Request $request)
    {
        $request->validate(
            [
                'ozonCategoryId' => 'required|int',
                'brand' => 'required|string',
                'searchQueryTitle' => 'required|string',
                'characteristicTitle.*' => 'string',
            ]
        );

        $ozonCategoryId = $request->get('ozonCategoryId');
        $brand = $request->get('brand');
        $searchQueryTitle = $request->get('searchQueryTitle');
        $characteristicTitles = $request->get('characteristicTitle');

        $perfectTitle = $this->makePerfectTitle($ozonCategoryId, $brand, $searchQueryTitle, $characteristicTitles);

        $response = [
            'perfectTitle' => $perfectTitle,
        ];

        return response()->api(
            true,
            $response,
            []
        );
    }

    /***********************************/
    /***   Вспомогательные функции   ***/
    /***********************************/

    /**
     * Сформировать идеальный заголовок
     *
     * @param int      $ozonCategoryId
     * @param string   $brand
     * @param string   $searchQueryTitle
     * @param string[] $characteristicTitles
     *
     * @return string
     */
    protected function makePerfectTitle($ozonCategoryId, $brand, $searchQueryTitle, $characteristicTitles)
    {
        $searchQueryTitle = Str::ucfirst($searchQueryTitle);

        $perfectTitle = $searchQueryTitle . ' ' . $brand;

        if (!empty($characteristicTitles)) {
            $perfectTitle .= ', ' . Str::lower(implode(', ', $characteristicTitles));
        }

        return trim($perfectTitle);
    }

    /**
     * Выделить характеристики
     *
     * @param integer    $ozonCategoryId
     * @param string     $rootQueryTitle
     * @param Collection $searchQueries
     *
     * @return Collection
     */
    protected function detachCharacteristics($ozonCategoryId, $rootQueryTitle, $searchQueries)
    {
        // Выделяем характеристики из поисковых запросов
        $characteristics = [];

        /** @var SearchQuery $searchQuery */
        foreach ($searchQueries as $searchQuery) {
            $characteristics[] = $searchQuery->detachCharacteristic($rootQueryTitle);
        }

        // Собираем коллекцию уникальных характеристик
        $characteristicsCollection = new Collection();
        $i = 1;

        foreach ($characteristics as $key => &$name) {
            // Дедубликация
            foreach ($characteristics as $otherKey => $otherName) {
                if ($key != $otherKey) {
                    $name = str_replace($otherName, '', $name);
                }
            }

            // Убираем НЕ предлоги
            $words = explode(' ', $name);
            $words = array_filter($words, function ($word) use ($characteristicsCollection) {
                return mb_strlen($word) > 1 || $this->isPreposition($word);
            });
            $name = implode(' ', $words);

            // Убираем лишние пробелы
            $name = trim(preg_replace('/\s+/u', ' ', $name));

            // Если пустая
            if (mb_strlen($name) <= 1) {
                unset($name);
                continue;
            }

            // Для каждой характеристики - 2 поисковых запроса
            $queryA = $rootQueryTitle . ' ' . $name;
            $queryB = $name . ' ' . $rootQueryTitle;

            // Расчитываем рейтинг запросной характеристики
            $rating = 0;

            foreach ([$queryA, $queryB] as $query) {
                $rootQueriesSearchQueries = RootQuerySearchQueryRepository::findByTitleInCategory($ozonCategoryId, $query);

                foreach ($rootQueriesSearchQueries as $rootQuerySearchQuery) {
                    $rating += $rootQuerySearchQuery->additions_to_cart;
                }
            }

            if ($rating > 0) {
                $characteristicsCollection->push((object)['id' => $i++, 'name' => $name, 'rating' => $rating]);
            }
        }

        return $characteristicsCollection->sortByDesc('rating')->values();
    }

    /**
     * Функция-проверка: это предлог?
     *
     * @param string $word
     *
     * @return bool
     */
    protected function isPreposition($word)
    {
        $prepositions = ['в', 'с', 'о'];
        return in_array($word, $prepositions);
    }
}
