<?php

namespace App\Http\Controllers;

use App\Models\OzProduct;
use Illuminate\Http\Request;

class OzonSemanticController extends Controller
{
    public function  index ()
    {
        $products= OzProduct::select() //->first()->toArray();
            ->leftJoin('oz_categories', 'oz_categories.id', '=', 'oz_products.category_id')
       // ->pluck('oz_products.id', 'oz_categories.id')
        ->get(['oz_products.id AS product_id', 'oz_categories.name AS  category_name'])
        ->chunk(5)
        ->toArray();


        dd($products);
        $prodduct = OzProduct::select('id')->first()->toArray();

        dd($prodduct);

    }
}
