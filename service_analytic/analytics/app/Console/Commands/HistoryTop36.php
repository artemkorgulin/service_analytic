<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Models\Static\WbHistoryProduct;
use App\Models\Static\WbHistoryTop36;
use App\Repositories\V1\PositionRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HistoryTop36 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:top36 {period?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка данных по товарам WB из ТОП36';

    private HistoryRepositoryInterface $historyProductRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(HistoryRepositoryInterface $historyProductRepository)
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
        $wbProducts = WbHistoryProduct::query()->distinct('vendor_code')->cursor();
        $positionRepository = new PositionRepository();

        while ($period > 0) {
            $endDate = Carbon::now()->subDays($period)->format('Y-m-d');

            foreach ($wbProducts as $product) {
                $this->info($product->vendor_code);

                $recommendations = $this->historyProductRepository->getRecommendationsForTop36($product->vendor_code)->first();
                $positionCategory = round($positionRepository->getPositionCategory($product, $endDate)->avg('position'));
                $positionSearch = round($positionRepository->getPositionSearch($product, $endDate)->avg('position'));

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

                $wbHistoryProductId = WbHistoryTop36::query()->insertOrIgnore($data);

                WbHistoryProduct::query()->where('vendor_code', '=', $product->vendor_code)->where('date', '=', $now)->update(['id_history_top36' => $wbHistoryProductId ?? null]);
            }
            $period--;
        }
        $this->info('Данные WB HistoryTop36 актуализированы');

        return Command::SUCCESS;
    }
}
