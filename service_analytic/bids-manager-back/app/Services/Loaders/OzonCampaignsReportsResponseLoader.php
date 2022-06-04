<?php

namespace App\Services\Loaders;

use App\DataTransferObjects\AccountDTO;
use App\Enums\OzonPerformance\Campaign\CampaignReportState;
use App\Models\CronUUIDReport;
use App\Services\CsvService;
use App\Services\OzonPerformanceService;
use App\Services\UserService;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use function Co\run;

class OzonCampaignsReportsResponseLoader extends LoaderService
{

    private array $accountIds = [];


    public function __construct()
    {
        parent::__construct();
        $this->setCacheRepository(Cache::store('octane'));
    }


    /**
     * Загрузить отчеты из Озона
     *
     * @param  array  $params
     */
    protected function start($params = [])
    {
        echo date('Y-m-d H:i:s').': Start report response'.PHP_EOL;

        $groupedReports = CronUUIDReport::query()
            ->unprocessed()
            ->when($this->accountIds, function (Builder $query, $accountIds) {
                $query->whereIn('account_id', $accountIds);
            })
            ->whereNull('files')
            ->where('state', CampaignReportState::OK())
            ->get()
            ->groupBy('account_id');

        $accounts = UserService::getAdmAccounts(accountIds: $groupedReports->keys())->keyBy('id');

        run(function () use ($groupedReports, $accounts) {
            /** @var \Illuminate\Database\Eloquent\Collection<CronUUIDReport> $reports */
            foreach ($groupedReports as $accountId => $reports) {
                go(function () use ($reports, $accounts, $accountId) {
                    printf('Start downloading reports for account %d'.PHP_EOL, $accountId);
                    $account = $accounts[$accountId];
                    $ozonConnection = OzonPerformanceService::connectRepeat($account, $this->client,
                        $this->cacheRepository);
                    if (!isset($ozonConnection)) {
                        echo 'No Ozon Connection'.PHP_EOL;

                        return;
                    }

                    /** @var CronUUIDReport $report */
                    foreach ($reports as $report) {
                        printf('Downloading account %d report %s'.PHP_EOL, $accountId, $report->uuid);
                        $uuid = $report->uuid;

                        // Получаем отчет(ы)
                        $result = $ozonConnection->getClientStatisticsReport($uuid);
                        if (!$result) {
                            $this->handleError($report, $account, $ozonConnection);
                            continue;
                        }

                        $files         = $this->getFiles($result, $report);
                        $report->files = $files;
                        $report->save();
                    }
                });
            }
        });

        echo date('Y-m-d H:i:s').': All reports were processed'.PHP_EOL;
    }



    /**
     * @param  \App\Models\CronUUIDReport  $report
     * @param  \App\DataTransferObjects\AccountDTO  $account
     * @param  \App\Services\OzonPerformanceService|bool  $ozonConnection
     *
     * @return void
     */
    function handleError(CronUUIDReport $report, AccountDTO $account, OzonPerformanceService|bool $ozonConnection): void
    {
        printf('Ozon Get Client Statistics Report %s Error'.PHP_EOL, $report->uuid);
        $this->outputAccountInfo($account);
        $lastError = $ozonConnection::getLastError();
        if (empty($lastError)) {
            return;
        }

        print_r(Arr::only($lastError, ['code', 'message']));

        switch ($lastError['code']) {
            //logged out
            case 403:
                OzonPerformanceService::connectRepeat($account, $this->client, $this->cacheRepository);
                break;
            //report has been deleted as outdated
            case 404:
                $report->state     = CampaignReportState::ERROR();
                $report->processed = true;
                $report->save();
        }
    }


    /**
     * @param  array  $accountIds
     */
    public function setAccountIds(array $accountIds): void
    {
        $this->accountIds = $accountIds;
    }


    /**
     * @param  mixed  $res
     * @param  string  $uuid
     *
     * @return array
     */
    private function getFiles(mixed $res, CronUUIDReport $report): array
    {
        // Если это архив
        if (CsvService::fileContentIsArchive($res)) {
            // Распаковываем его
            $files = CsvService::extractFilesFromZip($res, $report->uuid);
        } else {
            $filename = sprintf(
                '%s_%s-%s',
                implode('', $report->campaign_ids),
                $report->date_from->format('d.m.Y'),
                $report->date_to->format('d.m.Y')
            );

            $files = (array) CsvService::saveTemporaryCsv($res, $report->uuid, $filename);
        }

        return $files;
    }
}
