<?php


namespace App\Repositories\Frontend\Autoselect;

use App\Models\AutoselectResult as Model;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class AutoselectResultRepository
 *
 * @package App\Repositories\Frontend\Autoselect
 */
class AutoselectResultRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    /**
     * @param int   $autoselectParameterId
     * @param array $filter
     * @param array $order
     * @return Collection
     */
    public function getFilteredList(int $autoselectParameterId, array $filter = [], array $order = ['field' => 'id'])
    {
        $result = $this->startConditions()
            ->where('autoselect_parameter_id', $autoselectParameterId);
        foreach ($filter as $filterCondition) {
            $result->where($filterCondition['column'], $filterCondition['operation'] ?? '=', $filterCondition['value']);
        }
        $result->orderBy($order['field'], $order['direction'] ?? 'ASC')
            ->limit(300);
        return $result->get();
    }

    /**
     * @param int $autoselectParameterId
     * @return Collection
     */
    public function getListByParameterId(int $autoselectParameterId):Collection
    {
        $result = $this->startConditions()
            ->where('autoselect_parameter_id', $autoselectParameterId);
        return $result->get();
    }

    /**
     * @param int $autoselectParameterId
     * @return int
     */
    public function getTotalCountByParameterId(int $autoselectParameterId): int
    {
        $result = $this->startConditions()
            ->where('autoselect_parameter_id', $autoselectParameterId)
            ->count();
        return $result;
    }
}
