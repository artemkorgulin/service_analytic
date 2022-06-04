<?php

namespace App\Tasks\Autoselect;

use App\Repositories\Frontend\Autoselect\AutoselectResultRepository;
use App\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GetFilteredAutoselectResultsTask
 *
 * @package App\Tasks\Autoselect
 */
class GetFilteredAutoselectResultsTask extends Task
{
    /** @var AutoselectResultRepository */
    protected $repository;

    /**
     * GetFilteredAutoselectResultsTask constructor.
     */
    public function __construct()
    {
        $this->repository = new AutoselectResultRepository();
    }

    /**
     * @param int            $autoselectParameterId
     * @param array          $filter
     * @param array|string[] $order
     * @return Collection
     */
    public function run(int $autoselectParameterId,
                        array $filter = [],
                        array $order = ['field' => 'id']
    ): Collection
    {
        $autoselectResults = $this->repository->getFilteredList($autoselectParameterId, $filter, $order);
        return $autoselectResults;
    }
}
