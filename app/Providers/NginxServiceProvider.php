<?php

namespace App\Providers;

use App\Modules\Nginx\Facades\NginxFacade;
use Illuminate\Support\ServiceProvider;
use App\Modules\Nginx\Interfaces\NginxInterface;
use App\Modules\Nginx\Repositories\NginxRepository;
use App\Modules\Nginx\Services\NginxService;

class NginxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(NginxInterface::class, NginxRepository::class);

        $this->app->singleton('nginx-service', function() {
            return new NginxService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->alias('nginx-service', NginxFacade::class);
    }
}
