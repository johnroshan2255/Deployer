<?php

namespace App\Modules\Docker\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Docker\Facades\DockerFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartDockerContainers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server) {}

    public function handle(): void
    {
        $this->server->logStep("Starting Docker containers...");

        $dockerPath = base_path("deployments/{$this->server->branch_name}/docker");
        // Start Docker containers
        $result = DockerFacade::startDockerContainers($dockerPath, $this->server->branch_name);
        if ($result['success']) {
            $this->server->logStep("Docker containers started successfully.");
        } else {
            $this->server->logStep($result['message'], 'failed');
            $this->server->updateStatus('failed');
            return;
        }
    }
}
