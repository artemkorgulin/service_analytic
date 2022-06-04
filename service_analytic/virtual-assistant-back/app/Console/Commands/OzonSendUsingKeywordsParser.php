<?php

namespace App\Console\Commands;

use App\Models\OzListGoodsUser;
use App\Services\ParserRabbitService;
use Illuminate\Console\Command;

class OzonSendUsingKeywordsParser extends Command
{
    private const PER_PAGE = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:send-using-keywords-parser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send tracked ozon words to parser';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentPage = 1;

        $parserRabbitService = new ParserRabbitService(config('queue.connections.rabbitmq-parser-oz'));

        do {
            $usingKeywords = OzListGoodsUser::whereHas('product')->with('product')->paginate(self::PER_PAGE, ['*'], 'page', $currentPage);
            $result = [];
            foreach ($usingKeywords as $usingKeyword) {
                $messageForParser = [];
                $messageForParser['oz_product_id'] = $usingKeyword->oz_product_id;
                $messageForParser['name'] = $usingKeyword->key_request;
                $messageForParser['user_id'] = $usingKeyword->product->user_id;

                if ($usingKeyword->product->sku_fbo) {
                    $result[] = $this->getJsonForParser($usingKeyword->product->sku_fbo, $messageForParser);
                }

                if ($usingKeyword->product->sku_fbs) {
                    $result[] = $this->getJsonForParser($usingKeyword->product->sku_fbs, $messageForParser);
                }
            }

            $parserRabbitService->sendMessage($result);

            $currentPage++;
        } while ($usingKeywords->isNotEmpty());

        return true;
    }

    /**
     * @param  string  $sku
     * @param  array  $messageForParser
     * @return string
     */
    private function getJsonForParser(string $sku, array $messageForParser): string
    {
        $messageForParser['sku'] = $sku;

        return json_encode($messageForParser);
    }
}
