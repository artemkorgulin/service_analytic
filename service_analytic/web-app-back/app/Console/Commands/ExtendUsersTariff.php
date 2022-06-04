<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FtpService;
use App\Services\UserService;
use App\Services\V2\TariffActivityForExtendedUsersTarifService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ExtendUsersTariff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extend:tariff
                    {--input_name= : Имя входяшего файла на  FTP }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Активация  тарифа в тариф активити';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = 'ftp';
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $inputName = $this->option('input_name');

        if (!$inputName) {
            $this->info('Name is required');

            return CommandAlias::FAILURE;
        }

        $timeStart = microtime(true);
        $this->info('Add tariff activity');

        foreach ((new FtpService())->getRows($inputName, $this->connection) as $row) {

            if (!$row) {
                continue;
            }

            $row = explode(',', $row);

            try {
                $email = trim($row[0]);
                // добавление в таблицу тариф активити
                if ($user = User::where('email', '=', $email)->first()) {
                    (new TariffActivityForExtendedUsersTarifService($user))->activateSubscription();
                    (new UserService($user))->forgetTariffCache();
                }

            } catch (\Exception $exception) {
                report($exception);
                $this->error($exception->getMessage());
            }
        }

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $this->info('Add tariff activity finished '.$time);

        return CommandAlias::SUCCESS;
    }
}
