<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\AccountServices;
use App\Services\Billing\OrderService;
use App\Services\CompanyService;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Exception;
use Illuminate\Console\Command;
use function Swoole\Coroutine\run;

class HandleCompaniesAfterEndCorporateTariff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:handle-after-end-corporate-tariff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle companies after end corporate tariff';


    /**
     * @param OrderService $orderService
     * @param AccountServices $accountServices
     * @param CompanyService $companyService
     * @return bool
     */
    public function handle(OrderService $orderService, AccountServices $accountServices, CompanyService $companyService)
    {
        $companies = Company::cursor();

        $bar = $this->output->createProgressBar($companies->count());
        $bar->setFormat('debug');
        $this->info(date('Y-m-d H:i:s') . ' ' . $bar->start() . PHP_EOL);


        foreach ($companies as $company) {
            run(function () use ($orderService, $accountServices, $company, $bar, $companyService) {
                go(function () use ($orderService, $accountServices, $company, $bar, $companyService) {
                    try {
                        if (!$orderService->hasService($company, 'corp')) {
                            $owner = $company->getOwner();

                            $companyService->switchCompanyUsers(false, $company, $owner);

                            $order = $company->directOrders()->orderByDesc('end_at')->first();

                            if ($order) {
                                if ($owner) {
                                    if (date('Y-m-d') === $order->end_at?->format('Y-m-d')
                                        || date('Y-m-d') === $order->end_at?->addDay(7)->format('Y-m-d')) {
                                        UsersNotification::dispatch(
                                            'private_office.company_corporate_end',
                                            [['id' => $owner->id, 'lang' => 'ru', 'email' => $owner->email]],
                                            ['date' => $order->end_at?->addDay(14)->format('d.m.Y'), 'company' => $company->name]
                                        );
                                    }

                                    if (date('Y-m-d') >= $order->end_at?->addDay(14)->format('Y-m-d')) {
                                        $accountServices->transferAccounts($company->id, 0, $owner->id);
                                    }
                                } else {
                                    foreach ($company->accounts as $account) {
                                        $accountServices->deleteFromTrackingAccountProducts($account->platform_id, $account->id);
                                    }
                                }
                            }

                        } else {
                            $companyService->switchCompanyUsers(true, $company);
                        }
                    } catch (Exception $exception) {
                        report($exception);
                        $this->error(sprintf('%s company_id - %s error_code - %s error_message - %s', date('Y-m-d H:i:s'), $company->id, $exception->getCode(), $exception->getMessage()));
                    }

                    $this->info(date('Y-m-d H:i:s') . ' ' . $bar->advance() . PHP_EOL);
                });
            });
        }


        $this->info(date('Y-m-d H:i:s') . ' ' . $bar->finish() . PHP_EOL);
        $this->info(date('Y-m-d H:i:s') . ' script is finish');

        return true;
    }
}
