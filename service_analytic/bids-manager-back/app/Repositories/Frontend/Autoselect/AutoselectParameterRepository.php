<?php

namespace App\Repositories\Frontend\Autoselect;

use App\Models\AutoselectParameter as Model;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AutoselectParameterRepository
 *
 * @package App\Repositories\Frontend\Autoselect
 */
class AutoselectParameterRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    /**
     * @param $date
     * @return Collection
     */
    public function getListOlderThan($date): Collection
    {
        $result = $this->startConditions()
            ->where('request_time', '<', $date)
            ->get();
        return $result;
    }
}
