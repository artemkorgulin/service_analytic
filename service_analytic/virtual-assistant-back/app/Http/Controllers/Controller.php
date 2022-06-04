<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;

class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    protected $page;
    protected $lastPage;

    protected $pagination = 15;
    protected $paginationPostfix = '_ozon_products';

    protected $user;

    /**
     * Конструктор контроллера
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
//        $this->user = $request->get('user');
//        $this->page = abs((int)$request->page) ?: 1;
//        if (app()->runningInConsole() === false && $this->user) {
//            if ($request->perPage) {
//                $this->pagination = (int)$request->perPage;
//                Cache::put($this->user['id'] . $this->paginationPostfix, $this->pagination);
//            } else if (Cache::has($this->user['id'] . $this->paginationPostfix)) {
//                $this->pagination = Cache::get($this->user['id'] . $this->paginationPostfix);
//            }
//        }
    }
}
