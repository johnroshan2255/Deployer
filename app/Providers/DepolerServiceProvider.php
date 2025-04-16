<?php

namespace App\Providers;

use App\Modules\Deployer\Facades\DeployerFacade;
use App\Modules\Deployer\Interfaces\DeployerInterface;
use App\Modules\Deployer\Repositories\DeployerRepository;
use App\Modules\Deployer\Services\DeployerService;
use Illuminate\Support\ServiceProvider;

class DepolerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DeployerInterface::class, DeployerRepository::class);

        $this->app->singleton('deployer-service', function() {
            return new DeployerService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->alias('deployer-service', DeployerFacade::class);
    }
}
