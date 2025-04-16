<?php

namespace App\Modules\Deployer\Jobs;

use App\Modules\Deployer\Facades\DeployerFacade;
use App\Modules\Deployer\Models\DeployedServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeployServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server) {}

    public function handle(): void
    {
        if (!DeployerFacade::prepareDeploymentDirectory($this->server)) {

            $this->server->update(['status' => 'failed']);
            $this->server->logStep("Failed to prepare deployment directory.");
        }

        // Dispatch the batch of jobs for actual deployment steps
        DeployServerBatch::dispatch($this->server);
    }
}
