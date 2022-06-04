<?php

namespace App\Tasks\Autoselect;

use App\Repositories\Frontend\Autoselect\AutoselectResultRepository;
use App\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GetAutoselectResultsTask
 *
 * @package App\Tasks\Autoselect
 */
class GetAutoselectResultsTask extends Task
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
     * @return Collection
     */
    public function run(int $autoselectParameterId): Collection
    {
        $autoselectResults = $this->repository->getListByParameterId($autoselectParameterId);
        return $autoselectResults;
    }
}
