<?php

namespace App\Classes;

use App\Classes\Parser\Api;
use App\Models\PlatfomSemantic;
use App\Models\WbProduct;
use App\Models\WbUsingKeyword;

class WbUsingKeywordsTestDataHandler
{
    private Api $api;

    /**
     * @param  Api  $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @param $productId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($productId): bool
    {
        $wbProduct = WbProduct::where('id', $productId)->firstOrFail();

        if (strlen($wbProduct->nmid) > 8) {
            return false;
        }

        $wbProductParse = $this->api->getParsingProduct($wbProduct->nmid);

        if (!$wbProductParse) {
            return false;
        }

        $categoreTree = $this->api->getCategoryTree($wbProductParse->category[0]->web_id,
            $wbProductParse->category[0]->subject_id);

        $platfomSemantics = PlatfomSemantic::where(function ($q) use ($wbProductParse) {
            $q->where('search_responce', 'LIKE', '%'.$wbProductParse->brand[0]->brand.'%')
                ->where('search_responce', 'LIKE', '%'.$wbProductParse->category[0]->name.'%');
        });

        if (isset($wbProductParse->purpose[0])) {
            $platfomSemantics->orWhere(function ($q) use ($wbProductParse) {
                $q->where('search_responce', 'LIKE', '%'.$wbProductParse->purpose[0]->name.'%')
                    ->where('search_responce', 'LIKE', '%'.$wbProductParse->category[0]->name.'%');
            });
        }

        for ($i = 2; $i < count($categoreTree); $i++) {
            $platfomSemantics->orWhere('search_responce', 'LIKE', '%'.$categoreTree[$i]->name.'%');
        }

        $platfomSemantics = $platfomSemantics->limit(150)->get()->keyBy('id');

        if (!$platfomSemantics->isEmpty()) {
            $map = [];
            //отбираем значения с одинаковыми key_request с максимальной popularity
            foreach ($platfomSemantics as $platfomSemantic) {
                if (!isset($map[$platfomSemantic->key_request])
                    || $map[$platfomSemantic->key_request]['popularity'] < $platfomSemantic->popularity) {
                    $map[$platfomSemantic->key_request] = [
                        'id' => $platfomSemantic->id,
                        'popularity' => $platfomSemantic->popularity,
                    ];
                }
            }
            //сортируем по popularity
            usort($map, function ($item1, $item2) {
                return $item2['popularity'] <=> $item1['popularity'];
            });
            $map = array_slice($map, 0, 30);

            foreach ($map as $element) {
                $platfomSemantic = $platfomSemantics[$element['id']];
                WbUsingKeyword::create([
                    'wb_product_id' => $productId,
                    'name' => $platfomSemantic->key_request,
                    'popularity' => $platfomSemantic->popularity,
                    'conv' => $platfomSemantic->conversion,
                    'section' => 1,
                ]);
            }
        }

        return true;
    }
}
