<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Http\Requests\Admin\UserRequest;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Show users journal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.roles.index');
    }

    /**
     * Store new user
     *
     * @param  RoleRequest $request
     * @throws Exception
     */
    public function store(RoleRequest $request)
    {
        ModelHelper::transaction(function () {

        });
    }

    /**
     * Update user
     *
     * @param  RoleRequest $request
     * @throws Exception
     */
    public function update(RoleRequest $request)
    {
        ModelHelper::transaction(function () {

        });
    }

    /**
     * Delete user
     *
     * @param  Request  $request
     * @throws Exception
     */
    public function destroy(Request $request)
    {
        ModelHelper::transaction(function () {

        });
    }
}
