<?php

namespace App\Providers;

use App\Modules\Deployer\Interfaces\DeployerInterface;
use App\Modules\Deployer\Repositories\DeployerRepository;
use Illuminate\Support\ServiceProvider;

class DepolerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DeployerInterface::class, DeployerRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
