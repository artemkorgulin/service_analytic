<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DashboardUpdateJob;

class UpdateDashboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all account dashboards data.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach ([1,3] as $marketplaiceId) {
            DashboardUpdateJob::dispatch($marketplaiceId);
        }

        return Command::SUCCESS;
    }
}
