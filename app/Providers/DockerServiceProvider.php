<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Docker\Interfaces\DockerInterface;
use App\Modules\Docker\Repositories\DockerRepository
;
class DockerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DockerInterface::class, DockerRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
