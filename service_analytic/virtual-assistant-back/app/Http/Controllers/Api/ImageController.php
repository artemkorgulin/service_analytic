<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageRequest;
use App\Models\Image;
use App\Services\UserService;

class ImageController extends Controller
{

    public $user_id;
    public $account_id;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user_id = UserService::getUserId();
        $this->account_id = UserService::getAccountId();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->api_success([Image::where([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id
        ])->paginate()], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ImageRequest $request)
    {
        if ($request->file()) {
            $fileName = time() . '_' . $request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('uploads/image', $fileName, 'public');
            $image = Image::create(
                [
                    'user_id' => $this->user_id,
                    'account_id' => $this->account_id,
                    'url' => '/storage/' . $filePath . time() . '_' . $request->file->getClientOriginalName(),
                    'title' => $request->get('title'),
                    'comment' => $request->get('comment'),
                ]
            );
            return response()->api_success([$image], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->api_success([Image::firstWhere([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'id' => $id])], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ImageRequest $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // //
    }
}
