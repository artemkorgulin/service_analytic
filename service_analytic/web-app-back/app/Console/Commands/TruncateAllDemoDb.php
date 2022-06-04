<?php

namespace App\Console\Commands;

use App\Services\Demo\AppService;
use App\Services\Demo\TruncateAllDatabasesService;
use Illuminate\Console\Command;

class TruncateAllDemoDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:truncate_all_db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка всех баз данных на сервере demo';

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
        if (!(new AppService())->isDemoServer()) {
            $this->error('Команда может быть запущена только на демо сервере');

            return Command::FAILURE;
        }

        try {
            (new TruncateAllDatabasesService())->run();
        } catch (\Exception $exception) {
            report($exception);
            $this->error('Очистка баз данных не удалась ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $this->info('Очистка баз данных завершена');

        return Command::SUCCESS;
    }
}
