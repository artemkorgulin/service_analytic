<?php

namespace App\Console\Commands;

use App\Models\OzProduct;
use App\Services\InnerService;
use App\Services\Ozon\OzonAnalyticsDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class OzonAnalyticsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:analytics-data
            {--date_from= : Начальная дата отчета в формате 2021-07-22, default это вчера }
            {--date_to= : Конечная дата получения отчета в формате 2021-07-23, default это вчера }
            {--product_id= : ID продукта }
            {--show : Отображение данных в консоли }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение аналитических данных по товарам Ozon';

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
     * @throws \Exception
     */
    public function handle()
    {
        // set dates from and to
        $date_from = $this->option('date_from');
        $date_to = $this->option('date_to');
        if (!$date_from || !$date_to) {
            $date_from = Carbon::yesterday()->format('Y-m-d');
            $date_to = Carbon::now()->format('Y-m-d');
        }
        $showInfo = $this->option('show') ? true : false;

        // get products
        $products = OzProduct::query();
        if (!empty($this->option('product_id'))) {
            $products->where('id', '=', (int)$this->option('product_id'));
        }
        foreach ($products->cursor() as $product) {
            // skip if hasn't account
            if (!isset($product->account_id)) {
                continue;
            }

            try {
                // get account
                $userAccount = Cache::remember('userAccount.' . $product->account_id, 300, function () use ($product) {
                    return (new InnerService())->getAccount($product->account_id);
                });
                if (!isset($userAccount['platform_client_id']) || !isset($userAccount['platform_api_key'])) {
                    continue;
                }

                // get metrics from ozon and save it
                $result = (new OzonAnalyticsDataService($userAccount))->updateMetrics($product, $date_from, $date_to);

                // show result in console
                if ($showInfo && !empty($result)) {
                    foreach ($result as $day => $metrics) {
                        $rows = [];
                        $num = 0;
                        if (!empty($metrics)) {
                            foreach ($metrics as $metricName => $metricVal) {
                                $rows[] = [
                                    'Product ID' => $product->id,
                                    'SKU FBO' => $product->sku_fbo,
                                    'SKU FBS' => $product->sku_fbs,
                                    'Day' => $day,
                                    '#' => ++$num,
                                    'Metric' => $metricName,
                                    'Value' => (strlen($metricVal) > 100) ? substr($metricVal, 0, 100) . '..' : $metricVal
                                ];
                            }
                        }
                        if (!empty($rows)) {
                            $this->table(['Product ID', 'SKU FBO', 'SKU FBS', 'Day', '#', 'Metric', 'Value'], $rows);
                        } else {
                            $this->error('Product ID: ' . $product->id . ' / Day: ' . $day . ' - No result!');
                        }
                    }
                }

            } catch (\Exception $exception) {
                report($exception);
                $this->error(sprintf('Произошла ошибка при обновлении значений продукта - %s: %s', $product->id, $exception->getMessage()));
            }

            sleep(1);
        }
    }
}
