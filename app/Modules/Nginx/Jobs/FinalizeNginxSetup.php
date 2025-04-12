<?php

namespace App\Modules\Nginx\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Nginx\Interfaces\NginxInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeNginxSetup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server, private NginxInterface $nginx) {}

    public function handle(): void
    {
        $this->server->logStep("Finalizing Nginx setup...");

        $nginxPath = base_path("deployments/{$this->server->branch}/nginx/config");

        // Finalize Nginx setup
        $result = $this->nginx->finalizeNginxSetup($nginxPath, $this->server->branch);
        if ($result) {
            $this->server->logStep("Nginx setup finalized successfully.");
        } else {
            $this->server->logStep("Failed to finalize Nginx setup.");
            $this->server->updateStatus('failed');
            return;
        }
    }
}
