<?php

namespace App\Modules\Nginx\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Nginx\Facades\NginxFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeNginxSetup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server) {}

    public function handle(): void
    {
        $this->server->logStep("Finalizing Nginx setup...");

        $nginxPath = base_path("deployments/{$this->server->branch_name}/nginx/config");

        // Finalize Nginx setup
        $result = NginxFacade::finalizeNginxSetup($nginxPath, $this->server->branch_name);
        if ($result['success']) {
            $this->server->logStep("Nginx setup finalized successfully.");
        } else {
            $this->server->logStep($result['message'], 'failed');
            $this->server->updateStatus('failed');
            return;
        }
    }
}
