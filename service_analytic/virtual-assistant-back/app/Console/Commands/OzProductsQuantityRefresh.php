<?php

namespace App\Console\Commands;

use App\Constants\PlatformConstants;
use App\Services\V2\OzonStocksService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function Swoole\Coroutine\run;

class OzProductsQuantityRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:update-stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh oz_products quantity';

    /**
     * Execute the console command.
     * @TODO Убрать подключение к базе webapp , переделать на апи.
     */
    public function handle(OzonStocksService $ozonStocksService)
    {
        $accounts = DB::connection('wab')
            ->table('accounts')
            ->select('accounts.platform_client_id', 'accounts.platform_api_key', 'accounts.id')
            ->where('accounts.platform_id', PlatformConstants::OZON_PLATFORM_ID)
            ->get();

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->setFormat('debug');
        $this->info(date('Y-m-d H:i:s') . " " . $bar->start() . "\n");

        foreach ($accounts as $account) {
            run(function () use ($ozonStocksService, $account, $bar) {
                go(function () use ($ozonStocksService, $account, $bar) {
                    try {
                        $ozonStocksService->updateStocks(
                            $account->id,
                            $account->platform_client_id,
                            $account->platform_api_key
                        );
                    } catch (Exception $exception) {
                        report($exception);
                        $this->error(date('Y-m-d H:i:s') . " " . 'account_id - ' . $account->id . ' error_code - ' . $exception->getCode() . ' error_message - ' . $exception->getMessage());
                    }

                    $this->info(date('Y-m-d H:i:s') . " " . $bar->advance() . "\n");
                });
            });
        }

        $this->info(date('Y-m-d H:i:s') . " " . $bar->finish() . "\n");
        $this->info(date('Y-m-d H:i:s') . " " . "script worked");
    }
}
