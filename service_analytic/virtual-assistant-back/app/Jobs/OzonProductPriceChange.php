<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OzonProductPriceChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $host = 'https://api-seller.ozon.ru';
    protected $account;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($account, $data)
    {
        $this->account = $account;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Use for price change only this method - other is not correct
        $ch = curl_init($this->host . '/v1/product/import/prices');
        $request = (object)['prices' => [(object)$this->data]];
        $payload = json_encode($request);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Client-Id: {$this->account['platform_client_id']}",
            "Api-Key: {$this->account['platform_api_key']}",
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}
