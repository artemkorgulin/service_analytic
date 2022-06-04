<?php

namespace App\Console\Commands;

use App\Models\OzonProxy;
use App\Models\OzProduct;
use App\Services\Ozon\OzonParsingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ParseOzonCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:parse-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle(OzonParsingService $ozonParsingService)
    {
        $proxies = OzonProxy::get();
        $bar = $this->output->createProgressBar(OzProduct::withTrashed()->count('id'));
        $bar->start();
        OzProduct::withTrashed()->chunk(100, function ($products) use ($proxies, $bar, $ozonParsingService) {
            foreach ($products as $product) {
                $proxy = $proxies->shuffle()->first();
                $category = $ozonParsingService->parseOzonCategory($product->url, $proxy);
                $ozonParsingService->updateProductCategory($product->id, $category);
                $bar->advance();
            }
        });
        $bar->finish();
        return 0;
    }
}
