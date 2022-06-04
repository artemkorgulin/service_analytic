<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Models\Static\OzHistoryProduct;
use App\Repositories\V1\Action\OzHistoryProductRepository;
use App\Repositories\V1\Assistant\OzProductAnalyticsDataRepository;
use App\Repositories\V1\OzPositionRepository;
use App\Repositories\V1\Assistant\OzProductRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OzHistoryMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oz_history:migrate {period?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка данных по Ozon товарам из виртуального помощника';
    private HistoryRepositoryInterface $historyProductRepository;
    private OzPositionRepository $positionRepository;
    private OzProductAnalyticsDataRepository $ozProductAnalyticsDataRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        OzHistoryProductRepository $historyProductRepository,
        OzPositionRepository $positionRepository,
        OzProductAnalyticsDataRepository $ozProductAnalyticsDataRepository
    ) {
        parent::__construct();
        $this->historyProductRepository = $historyProductRepository;
        $this->positionRepository = $positionRepository;
        $this->ozProductAnalyticsDataRepository = $ozProductAnalyticsDataRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $period = $this->argument('period') ?? 1;
        $products = OzProductRepository::startConditions()->cursor();

        while ($period > 0) {
            $date = (Carbon::now())->subDays($period)->format('Y-m-d');

            $data = [];
            $skuArray = [];
            foreach ($products as $product) {
                if (!$product->sku_fbo) {
                    continue;
                }

                $dataId[] = $product->sku_fbo;
                $cardProduct = $this->historyProductRepository->getProductByVendorCode((int) $product->sku_fbo, $date);

                $this->info($product->sku_fbo);
                $skuArray[] = $product->sku_fbo;
                $productInfo = $this->historyProductRepository->getProductByProductInfo((int) $product->sku_fbo, $date);
                $positionSearch = $this->positionRepository->getPositionSearchByProduct($product, $date);
                $sales =  $this->ozProductAnalyticsDataRepository->getSalesByOzProductAnalyticsData((int) $product->sku_fbo, $date);

                $data[] = [
                    'user_id' => $product->user_id,
                    'account_id' => $product->account_id,
                    'name' => $product->name,
                    'vendor_code' => $product->sku_fbo,
                    'category_id' => $cardProduct->web_id ?? 0,
                    'subject_id' => $cardProduct->subject_id ?? 0,
                    'position_category' => $cardProduct->position ?? 0,
                    'position_search' => $positionSearch ?? 0,
                    'date' => $date,
                    'rating' => isset($productInfo->grade) ? round($productInfo->grade) : 0,
                    'optimization' => (int) $product->optimization ?? 0,
                    'comments' => $productInfo->comments_count ?? 0,
                    'images' => $productInfo->images_count ?? 0,
                    'escrow' => 0,
                    'current_sales' => isset($sales->report_date) ? (int) $sales->delivered_units : 0,
                    'id_history_top36' => 0,
                    'url' => $product->url ?? '',
                ];

                OzHistoryProduct::query()->insertOrIgnore($data);
                unset($data);
            }
            Artisan::call('clear:history', [
                'model' => OzHistoryProduct::class , 'sku-array' => $skuArray
            ]);
            $period--;
        }
        $this->info('Данные по товарам ozon актуализированы');

        return self::SUCCESS;
    }
}
