<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Repositories\Frontend\Keyword\KeywordRepository;
use App\Http\Requests\V2\Keyword\KeywordGetListByFilterRequest;
use Illuminate\Pagination\Paginator;

/**
 * Class KeywordController
 *
 * @package App\Http\Controllers\Api\V2
 */
class KeywordController extends Controller
{
    /**
     * @param  KeywordGetListByFilterRequest  $request
     * @return mixed
     */
    public function index(KeywordGetListByFilterRequest $request)
    {
        $keywordRepository = new KeywordRepository();
        /** @var Paginator $result */
        $result = $keywordRepository->getKeywordsSearch($request);
        $response = array_merge(
            $result->toArray(),
            ['success' => true, 'errors' => []]
        );

        return response()->json($response);
    }
}
