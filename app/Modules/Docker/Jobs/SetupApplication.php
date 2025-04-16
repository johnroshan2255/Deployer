<?php

namespace App\Modules\Docker\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Docker\Facades\DockerFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetupApplication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server) {}

    public function handle(): void
    {
        $this->server->logStep("Setting up application...");
        // Start Laravel Setup
        $result = DockerFacade::setupLaravelApp($this->server->branch_name);
        if ($result['success']) {
            $this->server->logStep("Setup completed successfully.");
        } else {
            $this->server->logStep($result['message'], 'failed');
            $this->server->updateStatus('failed');
            return;
        }
    }
}
