<?php

namespace App\Console\Commands;

use App\Services\Escrow\EscrowService;
use Illuminate\Console\Command;

class ClearEscrowHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escrow:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command clear escrow histories table';

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
    public function handle(EscrowService $escrowService)
    {
        $escrowService->clearEscrowHistory();
        return Command::SUCCESS;
    }
}
