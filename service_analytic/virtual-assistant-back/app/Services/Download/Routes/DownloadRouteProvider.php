<?php

namespace App\Services\Download\Routes;

use App\Http\Controllers\DownloadController;
use App\Http\Middleware\WebAppAuth;
use Illuminate\Support\Facades\Route;

class DownloadRouteProvider
{
    private array $middleware = [
        WebAppAuth::class
    ];

    public function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'download',
            'middleware' => $this->middleware
        ], function () {
            Route::get(
                '/abuse-pdf/{id}',
                [DownloadController::class, 'abuse']
            )->name(DownloadRoutes::ROUTE_ABUSE_PDF);
        });
    }
}
