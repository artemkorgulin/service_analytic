<?php

namespace App\Console\Commands\Ozon;

use App\Console\Commands\AcceptsCommaSeparatedArrayOptions;
use App\Enums\OzonPerformance\Campaign\CampaignReportState;
use App\Models\CronUUIDReport;
use App\Services\OzonPerformanceService;
use App\Services\OzonService;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UpdateReportStates extends Command
{

    use AcceptsCommaSeparatedArrayOptions;

    const CHUNK_SIZE = 200;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:update-report-states
                                {--A|--accounts= : Account ids, e.g. 1,2,3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновить статусы запрошенных отчётов';

    private Repository $cacheRepository;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->cacheRepository = (new OzonService())->useOctaneCacheRepository();
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $accountIds = $this->getArrayOption('accounts');

        CronUUIDReport::query()
            ->unprocessed()
            ->when($accountIds, function (Builder $query, $accountIds) {
                $query->whereIn('account_id', $accountIds);
            })
            ->whereIn('state', CampaignReportState::UNCOMPLETED_STATES())
            ->chunk(self::CHUNK_SIZE, function (Collection $reports) {
                $accountId = $reports->pluck('account_id')->unique()->first();
                $account   = UserService::findAdmAccount($accountId);

                if (!$account) {
                    $this->error(sprintf('Account %d wasn\'t found', $accountId));

                    return;
                }

                $this->info(sprintf('Processing reports for account %d', $accountId));
                $ozonService = OzonPerformanceService::connectRepeat($account);

                $uuids = [];

                /** @var CronUUIDReport $report */
                foreach ($reports as $report) {
                    $reportState = $ozonService->getClientStatisticsReportState($report->uuid);

                    if (!$reportState) {
                        OzonPerformanceService::handleError($account);
                        continue;
                    }

                    if ($report->state != $reportState->state) {
                        $uuids[]       = $report->uuid;
                        $report->state = $reportState->state;
                        $report->update();
                    }
                }

                if ($uuids) {
                    $this->info(sprintf('Updated states for account %d reports:', $accountId));
                    foreach ($uuids as $uuid) {
                        $this->line($uuid);
                    }
                }
            });

        return self::SUCCESS;
    }
}
