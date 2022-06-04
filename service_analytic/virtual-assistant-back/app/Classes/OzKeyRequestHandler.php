<?php

namespace App\Classes;

use App\Models\OzListGoodsAdd;
use App\Models\OzListGoodsUser;
use App\Models\PlatfomSemantic;
use Illuminate\Http\Request;

class OzKeyRequestHandler
{
    /**
     * @param  int  $productId
     */
    public function getListGoods($productId): array
    {
        $ozListProducts = PlatfomSemantic::where('product_id', $productId)
            ->orderBy('popularity', 'desc')->groupBy('search_responce')->limit(60)->get()->toArray();

        return $ozListProducts;
    }

    /**
     * @param  array  $data
     * @param  int  $productId
     * @return void
     */
        public function createListGoodsAdds(array $data, int $productId): void
        {
            foreach ($data as $element) {
                OzListGoodsAdd::create([
                    'oz_product_id' => $productId,
                    'key_request' => $element['name'],
                    'popularity' => $element['popularity'],
                ]);
            }
        }

    /**
     * @param  Request $request
     */
    public function createListGoodsUser(Request $request): void
    {
        foreach ($request->get('data') as $element) {
            OzListGoodsUser::create([
                'oz_product_id' => $element['product_id'],
                'key_request' => $element['key_request'],
                'popularity' => $element['popularity'],
                'conversion' => $element['conversion'],
                'section' =>  $element['section'],
            ]);
        }
    }

    /**
     * @param  int $productId
     */
    public function deleteListGoodsAdds(int $productId): void
    {
        OzListGoodsAdd::query()->where('oz_product_id', $productId)->delete();
    }
}
