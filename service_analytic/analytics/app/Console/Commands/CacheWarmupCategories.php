<?php

namespace App\Console\Commands;

use App\Models\Categories\CategoryTree;
use App\Repositories\V2\Categories\CategoryStatisticRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class CacheWarmupCategories extends Command
{
    protected const DIFF_DAYS = 30;
    protected const DEPTH_FOR_WARMUP = [1, 2];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warmup-cache:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up the statistics cache by categories for the last 30 days';

    private string $startDate;
    private string $endDate;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->startDate = Carbon::yesterday()->subDay(self::DIFF_DAYS - 1)->format('Y-m-d');
        $this->endDate = Carbon::yesterday()->format('Y-m-d');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $categories = CategoryTree::query()->select('path')
            ->whereIn('depth', self::DEPTH_FOR_WARMUP)->get();

        if ($categories->count() === 0) {
            $this->info('Category list is empty');
            return self::FAILURE;
        }

        $this->info(sprintf('Period from %s to %s', $this->startDate, $this->endDate));
        $requestParams = [
            'category' => '',
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'diffDate' => self::DIFF_DAYS
        ];
        foreach ($categories as $category) {
            $requestParams['category'] = $category->path;
            $this->info($this->warmUpCategory($requestParams));
        }

        return self::SUCCESS;
    }

    /**
     * @param $requestParams
     * @return string
     */
    private function warmUpCategory($requestParams): string
    {
        $countSuccessMethod = 0;
        try {
            (new CategoryStatisticRepository($requestParams))->getCachedProducts();
            $countSuccessMethod++;
        } catch (Exception $e) {
        }

        try {
            (new CategoryStatisticRepository($requestParams))->getCachedSubcategories();
            $countSuccessMethod++;
        } catch (Exception $e) {
        }

        try {
            (new CategoryStatisticRepository($requestParams))->getCachedPriceAnalysis();
            $countSuccessMethod++;
        } catch (Exception $e) {
        }

        return sprintf("Warm up '%s' - %s of 3", $requestParams['category'], $countSuccessMethod);
    }

}
