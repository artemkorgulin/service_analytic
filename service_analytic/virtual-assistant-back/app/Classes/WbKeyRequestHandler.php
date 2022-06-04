<?php

namespace App\Classes;

use App\Classes\Parser\Api;
use App\Models\PlatfomSemantic;
use App\Models\WbPickListProduct;
use App\Models\WbProduct;
use Illuminate\Support\Facades\DB;

class WbKeyRequestHandler
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($productId): void
    {
        $wbProduct = WbProduct::where('id', $productId)->firstOrFail();

        $wbPickListProducts = WbPickListProduct::where('wb_product_id', $wbProduct->id)->get();

        if ($wbPickListProducts->isEmpty()) {
            $wbProductParse = $this->api->getParsingProduct($wbProduct->nmid);

            if ($wbProductParse) {
                $categoreTree = $this->api->getCategoryTree($wbProductParse->category[0]->web_id,
                    $wbProductParse->category[0]->subject_id);

                //$platfomSemantics = PlatfomSemantic::where(DB::raw('1 = 1'));

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

                $platfomSemantics = $platfomSemantics->get()->keyBy('id');

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
                    $map = array_slice($map, 0, 60);

                    foreach ($map as $element) {
                        $platfomSemantic = $platfomSemantics[$element['id']];
                        WbPickListProduct::create([
                            'wb_product_id' => $productId,
                            'name' => $platfomSemantic->key_request,
                            'popularity' => $platfomSemantic->popularity,
                            'conv' => $platfomSemantic->conversion,
                        ]);
                    }
                }
            }
        }
    }
}
