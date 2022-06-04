<?php

namespace App\Console\Commands;

use App\Classes\Parser\Api;
use App\Classes\WbUsingKeywordsTestDataHandler;
use App\Models\WbCategory;
use App\Models\WbGuideProductCharacteristics;
use App\Models\WbProduct;
use Exception;
use Illuminate\Console\Command;
use stdClass;

class WbUsingKeywordsTestDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'using_keywords:generate {limit?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private WbUsingKeywordsTestDataHandler $handler;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WbUsingKeywordsTestDataHandler $handler)
    {
        parent::__construct();

        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = $this->argument('limit');
        $wbProducts = WbProduct::limit($limit)->get();

        foreach ($wbProducts as $wbProduct) {
            try {
                $this->handler->handle($wbProduct->id);
            } catch (Exception $exception) {
                report($exception);
            }
        }

        return 'success';
    }
}
