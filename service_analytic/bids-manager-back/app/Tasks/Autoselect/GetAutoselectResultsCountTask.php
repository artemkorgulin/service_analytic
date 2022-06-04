<?php

namespace App\Tasks\Autoselect;

use App\Repositories\Frontend\Autoselect\AutoselectResultRepository;
use App\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GetAutoselectResultsCountTask
 *
 * @package App\Tasks\Autoselect
 */
class GetAutoselectResultsCountTask extends Task
{
    /** @var AutoselectResultRepository */
    protected $repository;

    /**
     * GetAutoselectResultsCountTask constructor.
     */
    public function __construct()
    {
        $this->repository = new AutoselectResultRepository();
    }

    /**
     * @param int $autoselectParameterId
     * @return int
     */
    public function run(int $autoselectParameterId): int
    {
        $autoselectResultsTotal = $this->repository->getTotalCountByParameterId($autoselectParameterId);
        return $autoselectResultsTotal;
    }
}
