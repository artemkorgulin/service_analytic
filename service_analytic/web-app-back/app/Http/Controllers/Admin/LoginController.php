<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Auth;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function index()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->except(['_token']);

        try {
            if ($token = auth()->guard('api_v1')->attempt($credentials)) {
                $request->session()->push('api_v1', 'bearer '.$token);
            }
            if (Auth::guard('admin')->attempt($credentials)) {
                return redirect(RouteServiceProvider::HOME);
            }
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
            return redirect('/admin/login')->withErrors([__('errors.db_failed')]);
        }

        return redirect('/admin/login')->withErrors([__('auth.failed')]);
    }
}
