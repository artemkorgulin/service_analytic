<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Models\Static\OzHistoryProduct;
use App\Models\Static\OzHistoryTop36 as ModelOzHistoryTop36;
use App\Repositories\V1\Action\OzHistoryProductRepository;
use App\Repositories\V1\OzPositionRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OzHistoryTop36 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oz_history:top36 {period?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка данных по товарам Ozon из ТОП36';
    private HistoryRepositoryInterface $historyProductRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OzHistoryProductRepository $historyProductRepository)
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
        $now = (Carbon::now())->format('Y-m-d');
        $ozProducts = OzHistoryProduct::query()->distinct('vendor_code')->cursor();
        $positionRepository = new OzPositionRepository();

        while ($period > 0) {
            $endDate = Carbon::now()->subDays($period)->format('Y-m-d');

            foreach ($ozProducts as $product) {
                $this->info($product->vendor_code);

                $recommendations = $this->historyProductRepository->getRecommendationsForTop36($product->vendor_code)->first();
                $positionCategoryCollect = $positionRepository->getPositionCategory($product, $endDate);
                $positionCategory = round($positionCategoryCollect->avg('position'));
                $positionSearch = round($positionRepository->getPositionSearch($positionCategoryCollect, $endDate)->avg());

                $data = [
                    'vendor_code' => $product->vendor_code,
                    'category_id' => $product->category_id ?? 0,
                    'subject_id' => $product->subject_id ?? 0,
                    'date' => $endDate ?? $now,
                    'rating_avg' => $recommendations ? round($recommendations->rating_avg) : 0,
                    'comments_avg' => $recommendations->comments ?? 0,
                    'position' => $positionCategory ?? 0,
                    'position_category' => $positionCategory ?? 0,
                    'position_search' => $positionSearch ?? 0,
                    'images_avg' => $recommendations->photo ?? 0,
                    'sale_avg' => $recommendations->sale_avg ?? 0,
                ];

                $ozHistoryProductId = ModelOzHistoryTop36::query()->insertOrIgnore($data);

                OzHistoryProduct::query()->where('vendor_code', '=', $product->vendor_code)->where('date', '=', $now)->update(['id_history_top36' => $ozHistoryProductId]);
            }
            $period--;
        }
        $this->info('Данные HistoryTop36 ozon актуализированы');

        return Command::SUCCESS;
    }
}
