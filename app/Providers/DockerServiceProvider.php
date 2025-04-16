<?php

namespace App\Providers;

use App\Modules\Docker\Facades\DockerFacade;
use Illuminate\Support\ServiceProvider;
use App\Modules\Docker\Interfaces\DockerInterface;
use App\Modules\Docker\Repositories\DockerRepository
;
use App\Modules\Docker\Services\DockerService;

class DockerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DockerInterface::class, DockerRepository::class);

        $this->app->singleton('docker-service', function() {
            return new DockerService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->alias('docker-service', DockerFacade::class);
    }
}
