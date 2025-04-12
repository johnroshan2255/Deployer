<?php

namespace App\Modules\Docker\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Deployer\Traits\DeployTrait;
use App\Modules\Docker\Interfaces\DockerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDockerYmlFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DeployTrait;

    public function __construct(public DeployedServer $server, protected DockerInterface $docker) {}

    public function handle(): void
    {
        $this->server->logStep("Creating Docker compose file...");

        $dockerPath = base_path("deployments/{$this->server->branch}/docker");

        // Create YML file
        $result = $this->docker->generateDockerComposeFile($dockerPath, 8000, $this->server->branch);
        if ($result) {
            $this->server->logStep("Docker compose file created at {$dockerPath}");
        } else {
            $this->server->logStep("Failed to create Docker compose file at {$dockerPath}");
            $this->server->updateStatus('failed');
            return;
        }

    }
}

