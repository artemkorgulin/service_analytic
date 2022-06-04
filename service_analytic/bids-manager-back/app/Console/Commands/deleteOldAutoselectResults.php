<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use App\Models\AutoselectParameter;
use App\Repositories\Frontend\Autoselect\AutoselectParameterRepository;
use App\Repositories\Frontend\Autoselect\AutoselectResultRepository;

class deleteOldAutoselectResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autoselect:delete_old_results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Задание на очистку результатов автоподбора старше 3 дней';

    /** @var AutoselectParameterRepository $autoselectParameterRepository */
    protected $autoselectParameterRepository;

    /** @var AutoselectResultRepository $autoselectResultRepository */
    protected $autoselectResultRepository;

    /** @var int $offset */
    protected $offset = 3;


    public function __construct()
    {
        parent::__construct();
        $this->autoselectParameterRepository = new AutoselectParameterRepository();
        $this->autoselectResultRepository = new AutoselectResultRepository();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentTime = Carbon::now();
        $olderThan = $currentTime->subDays($this->offset);

        /** @var Collection $oldAutoselectParameterCollection */
        $oldAutoselectParameterCollection = $this->autoselectParameterRepository->getListOlderThan($olderThan);
        if ($oldAutoselectParameterCollection->count() == 0) {
            echo "{$currentTime} - Autoselect records older than {$olderThan} does not exists - nothing to delete. \n";
            return true;
        }

        /** @var AutoselectParameter $item */
        foreach ($oldAutoselectParameterCollection as $item) {
            $item->delete();
        }

        echo "{$currentTime} - Autoselect records older than {$olderThan} was successfully deleted. \n";
        return true;
    }
}
