<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CentralRoutesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $routesPath = base_path('routes/central.php');

        if (file_exists($routesPath)) {
            Route::group([], $routesPath);
        }
    }
}
