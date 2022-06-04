<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlackListBrand;
use Illuminate\Http\Request;

class BlackListBrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $response)
    {
        $search = $response->get('search');
        $blbs = BlackListBrand::select();
        if ($search) {
            $blbs = $blbs->where('brand', 'LIKE', '%'.$search.'%');
        }

        return response($blbs->paginate()->setPath(''))->header('Content-Type', 'application/json');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner' => [ 'max:255' ],
            'brand' => [ 'require', 'unique:black_list_brands', 'max:255' ],
            'manager' => ['max:255'],
            'wildberries' => ['boolean'],
            'ozon' => ['boolean'],
            'amazon' => ['boolean'],
        ]);

        try {
            return response(BlackListBrand::create([
                'partner' => $request->get('partner'),
                'brand' => $request->get('brand'),
                'manager' => $request->get('manager'),
                'status' => $request->get('status'),
                'wildberries' => $request->get('wildberries'),
                'ozon' => $request->get('ozon'),
                'amazon' => $request->get('amazon'),
            ]))->header('Content-Type', 'application/json');
        } catch(\Exception $exception) {
            report($exception);
            return response(['error' => $exception->getMessage()])->header('Content-Type', 'application/json');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response(BlackListBrand::findOrFail($id))->header('Content-Type', 'application/json');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $blb = BlackListBrand::findOrFail($id);

        $request->validate([
            'partner' => [ 'max:255' ],
            'brand' => [ 'require', 'unique:black_list_brands,brand,'.$blb->brand, 'max:255' ],
            'manager' => ['max:255'],
            'wildberries' => ['boolean'],
            'ozon' => ['boolean'],
            'amazon' => ['boolean'],
        ]);

        try {
            return response()->json($blb->update([
                'partner' => $request->get('partner'),
                'brand' => $request->get('brand'),
                'manager' => $request->get('manager'),
                'status' => $request->get('status'),
                'wildberries' => $request->get('wildberries'),
                'ozon' => $request->get('ozon'),
                'amazon' => $request->get('amazon'),
            ]));
        } catch(\Exception $exception) {
            report($exception);
            return response(['error' => $exception->getMessage()])->header('Content-Type', 'application/json');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blb = BlackListBrand::findOrFail($id);
        $blb->delete();
        return response(['deleted'])->header('Content-Type', 'application/json');
    }
}
