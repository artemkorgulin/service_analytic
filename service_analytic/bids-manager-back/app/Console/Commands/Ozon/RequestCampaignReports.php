<?php

namespace App\Console\Commands\Ozon;

use App\Console\Commands\AcceptsCommaSeparatedArrayOptions;
use App\DataTransferObjects\AccountDTO;
use App\Enums\OzonPerformance\Campaign\CampaignType as CampaignTypeEnum;
use App\Models\CampaignType;
use App\Models\CronUUIDReport;
use App\Services\CampaignService;
use App\Services\OzonPerformanceService;
use App\Services\UserService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use function Co\run;

class RequestCampaignReports extends Command
{

    use AcceptsCommaSeparatedArrayOptions;

    const CAMPAIGNS_PER_REQUEST     = 10; // Max campaigns per request
    const MAX_TRIES_ON_REQUEST      = 3;
    const MAX_TRIES_ON_LONG_REQUEST = 5;
    const SLEEP_COEFFICIENT         = 0.25;
    const ACCOUNTS_CHUNK_SIZE       = 50;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:request-campaign-reports 
                                {campaignIds?*}
                                {--A|--accounts= : Account ids, e.g. 1,2,3}
                                {--D|day : Generate reports for the day (Default value)}
                                {--Y|year : Generate reports for the last year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Задание на формирование отчетов о реализации в Озон';


    /**
     * Execute the console command.
     *
     * @return int|false
     */
    public function handle(): int|false
    {
        $campaignIds = $this->argument('campaignIds');

        $day        = $this->option('day');
        $year       = $this->option('year');
        $accountIds = $this->getArrayOption('accounts');

        if ($day && $year) {
            $this->error('You should provide only one date option at a time: --day or --year');

            return false;
        }

        if ($year) {
            $dateFrom = CarbonImmutable::tomorrow()->subYear();
        } else {
            $dateFrom = CarbonImmutable::today();
        }

        return $this->requestCampaignsReports($campaignIds, $dateFrom, $accountIds) ? self::SUCCESS : self::FAILURE;
    }


    /**
     * Generate reports by campaigns
     *
     * @param  int[]  $campaignIds
     * @param  \Carbon\CarbonImmutable  $dateFrom
     * @param  mixed  $accountIds
     *
     * @return bool
     */
    public function requestCampaignsReports(array $campaignIds, CarbonImmutable $dateFrom, mixed $accountIds): bool
    {
        // Для всех активных аккаунтов
        $accounts = UserService::getAdmAccounts(accountIds: $accountIds);
        if ($accounts->isEmpty()) {
            $this->error('Empty accounts list, finishing.');

            return false;
        }

        $this->info(date('Y-m-d H:i:s').': Start report requesting');

        $dateTo  = CarbonImmutable::tomorrow()->startOfDay();
        $groupBy = 'DATE';

        //todo: should we use other campaign types too?
        $campaignTypeId = CampaignType::type(CampaignTypeEnum::SKU())
            ->first()
            ->id;

        $accountsChunks = $accounts->chunk(self::ACCOUNTS_CHUNK_SIZE);
        foreach ($accountsChunks as $accountsChunk) {
            run(function () use ($accountsChunk, $campaignIds, $dateFrom, $dateTo, $campaignTypeId, $groupBy) {
                foreach ($accountsChunk as $account) {
                    go(function () use ($account, $campaignIds, $dateFrom, $dateTo, $campaignTypeId, $groupBy) {
                        $account = new AccountDTO((array) $account);
                        $this->requestCampaignsReportsForAccount($account, $campaignIds, $dateFrom, $dateTo, $campaignTypeId, $groupBy);
                    });
                }
            });
        }

        $this->info(date('Y-m-d H:i:s').': All reports were requested');

        return true;
    }


    /**
     * @param  AccountDTO  $account
     * @param  array|null  $campaignIds
     * @param  CarbonImmutable  $dateFrom
     * @param  CarbonImmutable  $dateTo
     * @param  int  $campaignTypeId
     * @param  string  $groupBy
     *
     * @return bool
     * @throws \Exception
     */
    private function requestCampaignsReportsForAccount(
        AccountDTO $account,
        ?array $campaignIds,
        CarbonImmutable $dateFrom,
        CarbonImmutable $dateTo,
        int $campaignTypeId,
        string $groupBy = 'DATE'
    ): bool {
        $ozonConnection = OzonPerformanceService::connectRepeat($account);
        if (!$ozonConnection) {
            return false;
        }

        // Собираем из базы список кампаний текущего аккаунта
        $campaigns              = CampaignService::getCampaignsForAccount($campaignIds, $account, $campaignTypeId);
        $campaignIds            = $campaigns->modelKeys();
        $chunkedCampaignIdsList = array_chunk($campaignIds, self::CAMPAIGNS_PER_REQUEST);

        foreach ($chunkedCampaignIdsList as $chunkCampaignIds) {
            $maxTries = $this->getMaxTries($dateFrom, $dateTo);
            $requestResult = false;
            for ($try = 0; $try < $maxTries; $try++) {
                $this->info(sprintf(
                    'Request client statistics for account %d campaigns: %s from %s to %s.',
                    $account->id,
                    implode(',', $chunkCampaignIds),
                    $dateFrom->toDateString(),
                    $dateTo->toDateString()
                ));

                if ($try >= 0) {
                    $this->info(sprintf('Account %d Try %d of %d', $account->id, $try + 1, $maxTries));
                }

                $requestResult = $ozonConnection->requestClientStatistics(
                    $chunkCampaignIds,
                    $dateFrom,
                    $dateTo,
                    $groupBy
                );

                if ($requestResult) {
                    break;
                }

                $errorIsSerious = OzonPerformanceService::handleError(
                    $account,
                    $this->getSleepTime($dateFrom, $dateTo, $try)
                );

                if ($errorIsSerious) {
                    $this->warn('Error is serious, aborting');

                    return false;
                }

                // Не тратим время, если все упало

            }

            if (!$requestResult) {
                $this->warn(sprintf('Tries limit exceeded. We\'re done for the account %d', $account->id));

                return false;
            }

            $this->info(sprintf('Saving report for account %d campaigns %s', $account->id,
                implode(',', $chunkCampaignIds)));
            CronUUIDReport::saveReportRequest($requestResult, $account, $dateFrom, $dateTo, $chunkCampaignIds);
            $this->info('saved');
            $this->newLine();
        }


        return true;
    }


    /**
     * Returns
     *
     * @param  \Carbon\CarbonImmutable  $dateFrom
     * @param  \Carbon\CarbonImmutable  $dateTo
     * @param  int  $try
     *
     * @return float
     */
    private function getSleepTime(CarbonImmutable $dateFrom, CarbonImmutable $dateTo, int $try): float
    {
        $days = $dateFrom->diffInDays($dateTo);

        if ($days <= 30) {
            return $days + (($try + 1) * 2) + 3;
        }

        return $days * self::SLEEP_COEFFICIENT;
    }


    /**
     * Get limit of request
     *
     * @param  \Carbon\CarbonImmutable  $dateFrom
     * @param  \Carbon\CarbonImmutable  $dateTo
     *
     * @return int
     */
    private function getMaxTries(CarbonImmutable $dateFrom, CarbonImmutable $dateTo): int
    {
        $days = $dateFrom->diffInDays($dateTo);

        return
            $days <= 30
                ? self::MAX_TRIES_ON_REQUEST
                : self::MAX_TRIES_ON_LONG_REQUEST;
    }
}
