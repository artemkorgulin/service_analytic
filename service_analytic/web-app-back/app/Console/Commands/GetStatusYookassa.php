<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use YooKassa\Client;

class GetStatusYookassa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yookassa:getInfo {paymentId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yookassa get info';
    private $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
        $this->client->setAuth(config('yookassa.shop_id'), config('yookassa.secret_key'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //dd($this->argument('paymentId'));
        $payment = $this->client->getPaymentInfo($this->argument('paymentId'));
        dd($payment);
    }
}
