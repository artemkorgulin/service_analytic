<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Models\Static\WbHistoryProduct;
use App\Repositories\V1\Action\WbHistoryProductRepository;
use App\Repositories\V1\Assistant\WbProductRepository;
use App\Services\CallsForActionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class HistoryMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:migrate {period?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка данных по товарам WB из виртуального помощника';

    private HistoryRepositoryInterface $historyProductRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WbHistoryProductRepository $historyProductRepository)
    {
        parent::__construct();
        $this->historyProductRepository = $historyProductRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $period = $this->argument('period') ?? 1;
        $products = WbProductRepository::startConditions()->cursor();
        $callsForActionService = new CallsForActionService();

        while ($period > 0) {
            $date = (Carbon::now())->subDays($period)->format('Y-m-d');

            $data = [];
            $skuArray = [];

            foreach ($products as $product) {
                if (!$product->sku) {
                    continue;
                }

                $this->info($product->sku);
                $skuArray[] = $product->sku;
                $cardProduct = $this->historyProductRepository->getProductByVendorCode((int) $product->sku, $date) ?? $callsForActionService->getInfoByCategoriesFilter($product);

                if (!empty($cardProduct)) {
                    $productInfo = $this->historyProductRepository->getProductByProductInfo($product->sku, $date);
                    $positionSearch = DB::table('search_requests as sr')->select('position')
                        ->where('vendor_code', '=', $product->sku)
                        ->where('subject_id', '=', $cardProduct->subject_id)
                        ->where('date', '=', $date)->get()->avg('position');
                }

                $data[] = [
                    'user_id' => $product->user_id,
                    'account_id' => $product->account_id,
                    'name' => $product->title,
                    'vendor_code' => $product->sku,
                    'category_id' => $cardProduct->web_id ?? 0,
                    'subject_id' => $cardProduct->subject_id ?? 0,
                    'position_category' => $cardProduct->position ?? 0,
                    'position_search' => isset($positionSearch) ? round($positionSearch) : 0,
                    'date' => $date,
                    'rating' => isset($product->rating) ? round($product->rating) : (isset($productInfo) ? $productInfo->grade : 0),
                    'optimization' => (int) $product->optimization ?? 0,
                    'comments' => isset($product->count_reviews) ? round($product->count_reviews) : ($productInfo->comments_count ?? 0),
                    'images' => $productInfo->images_count ?? 0,
                    'escrow' => $callsForActionService->getEscrowPercentForNomenclature($product, $product->nmid),
                    'current_sales' => $productInfo->current_sales ?? 0,
                    'id_history_top36' => 0,
                    'url' => $product->url ?? '',
                ];

                if (count($data) > 500) {
                    WbHistoryProduct::query()->insert($data);
                    unset($data);
                    $data = [];
                }
            }

            WbHistoryProduct::query()->insert($data);
            Artisan::call('clear:history', [
                'model' => WbHistoryProduct::class, 'sku-array' => $skuArray
            ]);
            $period--;
        }
        $this->info('Данные по товарам пользователей WB актуализированы');

        return self::SUCCESS;
    }
}
