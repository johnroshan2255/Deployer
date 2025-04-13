<?php

namespace App\Modules\Docker\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Docker\Interfaces\DockerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartDockerContainers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server, protected DockerInterface $docker) {}

    public function handle(): void
    {
        $this->server->logStep("Starting Docker containers...");

        $dockerPath = base_path("deployments/{$this->server->branch_name}/docker");
        // Start Docker containers
        $result = $this->docker->startDockerContainers($dockerPath, $this->server->branch_name);
        if ($result) {
            $this->server->logStep("Docker containers started successfully.");
        } else {
            $this->server->logStep("Failed to start Docker containers.");
            $this->server->updateStatus('failed');
            return;
        }
    }
}
