<?php

namespace App\Console\Commands;

use App\Models\WbUsingKeyword;
use App\Services\ParserRabbitService;
use Illuminate\Console\Command;

class WbUsingKeywordsParserInput extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb_using_keywords_parser:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $perPage = 1000;
        $currentPage = 1;

        $parserRabbitService = new ParserRabbitService(config('queue.connections.rabbitmq-parser-wb'));

        do {
            $usingKeywords = WbUsingKeyword::whereHas('product')->with('product')->paginate($perPage, ['*'], 'page',
                $currentPage);
            $result = [];
            foreach ($usingKeywords as $usingKeyword) {
                $object = [];
                $object['wb_product_id'] = $usingKeyword->wb_product_id;
                $object['name'] = $usingKeyword->name;
                $object['sku'] = $usingKeyword->product->nmid;
                $object['user_id'] = $usingKeyword->product->user_id;

                $result[] = json_encode($object);
            }
            $parserRabbitService->sendMessage($result);
            $currentPage++;
        } while ($usingKeywords->isNotEmpty());

        return true;
    }
}
