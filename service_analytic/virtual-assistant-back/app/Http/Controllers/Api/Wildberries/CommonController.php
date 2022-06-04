<?php

namespace App\Http\Controllers\Api\Wildberries;

use App\Helpers\Controller\CommonControllerHelper;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Services\Wildberries\Client;
use Illuminate\Http\Request;

class CommonController extends Controller
{

    protected $user;
    protected $account;
    protected $client;

    /**
     * Class constructor
     * @param Request $request
     * @throws \Exception
     */
    public function __construct(Request $request)
    {
        $commonControllerHelper = new CommonControllerHelper($request);
        $this->user = $commonControllerHelper->user;
        $this->account = $commonControllerHelper->account;
        $this->client = $commonControllerHelper->client;
    }

    /**
     * @param Request $request
     * @return CommonControllerHelper
     */
    protected function getParams(Request $request): CommonControllerHelper
    {
        return new CommonControllerHelper($request);
    }

    /**
     * @param Collection $collection
     * @param Request $request
     */
    protected function makeAppends(Request $request)
    {
        $appends = [];
        if ($request->get('sortBy')) {
            $appends['sortBy'] = $request->get('sortBy');
        }
        if ($request->get('sortType')) {
            $appends['sortType'] = $request->get('sortType');
        }
        if ($request->get('search')) {
            $appends['search'] = $request->get('search');
        }
        return $appends;
    }
}
