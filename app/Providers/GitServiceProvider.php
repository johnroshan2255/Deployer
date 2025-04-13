<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Git\Interfaces\GitInterface;
use App\Modules\Git\Repositories\GitRepository;

class GitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(GitInterface::class, GitRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
