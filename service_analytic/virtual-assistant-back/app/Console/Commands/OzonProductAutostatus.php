<?php

namespace App\Console\Commands;

use App\Jobs\CheckOzonProductStatus;
use App\Models\ProductChangeHistory;
use Illuminate\Console\Command;

class OzonProductAutostatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:products:autostatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускается для проверки статуса продуктов только если он равен 4';


    /**
     * Проверяем если только у продукта 4 статус
     * @var int
     */
    protected $statusToCheck = 4;

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
        ProductChangeHistory::where('status_id', $this->statusToCheck)
            ->where('task_id', '<>', 0)->get()->map(function($item){
                CheckOzonProductStatus::dispatch($item);
        });
        return 0;
    }
}
