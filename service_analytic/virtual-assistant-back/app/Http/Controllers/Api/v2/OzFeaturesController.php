<?php

namespace App\Http\Controllers\Api\v2;

use App\Models\Feature;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OzFeaturesController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param  int  $id  id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $search = $request->get('search');
        $options = Feature::whereExternalId($id)->first()->options();
        if ($search) {
            $options = $options->where('value', 'LIKE', '%'.$search.'%');
        }
        $options = $options->groupBy(['id'])->paginate();
        return response()->json($options);
    }

}
