<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use App\Services\Download\Routes\DownloadRouteProvider;

app(DownloadRouteProvider::class)->registerRoutes();
