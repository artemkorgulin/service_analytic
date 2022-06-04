<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMenuService;

class AppController extends Controller
{
    /**
     * Show the application index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.index');
    }

    /**
     * Get application menu.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMenu()
    {
        $menu = AdminMenuService::getMenu();

        return response()->api_success($menu, 200);
    }
}
