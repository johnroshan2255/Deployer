<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Deployer\Observers\DeployedServerObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DeployedServer::observe(DeployedServerObserver::class);
    }
}
