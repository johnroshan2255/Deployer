<?php

namespace App\Providers;

use App\Modules\Git\Facades\GitFacade;
use Illuminate\Support\ServiceProvider;
use App\Modules\Git\Interfaces\GitInterface;
use App\Modules\Git\Repositories\GitRepository;
use App\Modules\Git\Services\GitService;

class GitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(GitInterface::class, GitRepository::class);

        $this->app->singleton('git-service', function() {
            return new GitService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->alias('git-service', GitFacade::class);
    }
}
