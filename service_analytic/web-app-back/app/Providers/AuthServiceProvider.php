<?php

namespace App\Providers;

use App\Services\Auth\AdminGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::extend('admin', function($app, $name, array $config) {
            $guard = new AdminGuard(Auth::createUserProvider($config['provider']), $this->app['session.store']);

            /* Copy functionality form Auth::createSessionDriver */
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($this->app['cookie']);
            }
            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }
            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });

        view()->composer('admin.index', function ($view) {
            $view->with('token', session('api_v1'));
            $view->with('seller', []);
        });
    }
}
