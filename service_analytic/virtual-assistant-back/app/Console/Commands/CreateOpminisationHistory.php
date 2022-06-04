<?php

namespace App\Console\Commands;

use App\Models\OzProduct;
use App\Models\WbProduct;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateOpminisationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimisation:create-history {--fill= : Заполнить таблицы за последние дни}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполнение истории оптимизации товаров Ozon и Wildberries';

    const MAX_COROUTINES = 500;

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->getHistoryForProducts(OzProduct::class);
        $this->getHistoryForProducts(WbProduct::class);
    }

    /**
     * Method for calculate and store optimisation histories
     *
     * @param $model
     * @return void
     * @throws \Exception
     */
    private function getHistoryForProducts($model): void
    {
        if (!$this->option('fill')) {
            $datesForUpdate = [Carbon::now()];
        } else {
            for ($i = 0; $i < 30; $i++) {
                $datesForUpdate[] = Carbon::now()->subDays($i);
            }
        }
        $progressbar = $this->output->createProgressBar($model::count());
        $progressbar->start();
        $model::chunk(self::MAX_COROUTINES, function ($products) use ($datesForUpdate, $progressbar) {
            foreach ($products as $product) {
                foreach ($datesForUpdate as $date) {
                    ModelHelper::transaction(function () use ($product, $date, $progressbar) {
                        DB::table('optimisation_histories')->updateOrInsert([
                            'product_id' => $product->id,
                            'account_id' => $product->account_id,
                            'report_date' => $date->format('Y-m-d'),
                        ], [
                            'content_percent' => $product->content_optimization ?? 0,
                            'search_percent' => $product->search_optimization ?? 0,
                            'visibility_percent' => $product->visibility_optimization ?? 0,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                    });
                }
                $progressbar->advance();
            }
        });
        $progressbar->finish();
    }
}
