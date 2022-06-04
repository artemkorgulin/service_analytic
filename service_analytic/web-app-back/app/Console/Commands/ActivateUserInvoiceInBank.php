<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FtpService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class ActivateUserInvoiceInBank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activate:invoiceInBank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Активация  оплаты по счету ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info("Запущена команда Активация  оплаты по счету");
            $orders  = (new FtpService())->loadBankDirectory();
            $this->info("Активированы ордера: " . implode(", ",  $orders));
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Ошибка при выполнении команды активация оплаты по счету ');
        }

        $this->info("");

        return Command::SUCCESS;
    }
}
