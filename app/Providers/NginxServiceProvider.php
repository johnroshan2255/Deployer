<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Nginx\Interfaces\NginxInterface;
use App\Modules\Nginx\Repositories\NginxRepository;

class NginxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(NginxInterface::class, NginxRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
