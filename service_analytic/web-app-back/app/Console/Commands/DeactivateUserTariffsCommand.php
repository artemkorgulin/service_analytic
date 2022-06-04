<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Services\V2\PaymentService;

class DeactivateUserTariffsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deacivate:tariff {oderId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Дективации тарифа и отправка уведомление';

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
        // Активация тарифа и добавление в таблицу тариф активити
        $order = Order::whereId($this->argument('oderId'))->first();
        $user = User::whereId($order->user_id)->first();
        (new PaymentService($user, $order))->updateOrderInBank();

        return Command::SUCCESS;
    }
}
