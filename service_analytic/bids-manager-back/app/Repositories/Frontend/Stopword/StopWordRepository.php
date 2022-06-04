<?php


namespace App\Repositories\Frontend\Stopword;

use App\Http\Requests\V2\Stopword\StopWordGetListByFilterRequest;
use App\Models\StopWord as Model;
use App\Repositories\BaseRepository;
use AnalyticPlatform\LaravelHelpers\Helpers\PaginatorHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class StopWordRepository
 *
 * @package App\Repositories\Frontend\Stopword
 */
class StopWordRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    /**
     * @param  StopWordGetListByFilterRequest  $request
     * @return Collection|LengthAwarePaginator
     */
    public function getStopWordsSearch(StopWordGetListByFilterRequest $request)
    {
        $result = $this->startConditions()->select('id', 'name');

        if (!empty($request['search'])) {
            $result->where('name', 'like', '%' . $request['search'] .'%');
        }

        return PaginatorHelper::addPagination($request, $result);
    }

    /**
     * @param  string  $name
     * @return mixed
     */
    public function getByName(string $name)
    {
        return $this->startConditions()
            ->where('name', $name)
            ->first();
    }
}
