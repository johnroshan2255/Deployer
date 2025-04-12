<?php

namespace App\Modules\Nginx\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Nginx\Interfaces\NginxInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateNginxConfig implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server, private NginxInterface $nginx) {}

    public function handle(): void
    {
        $this->server->logStep("Creating Nginx configuration...");
        $nginxPath = base_path("deployments/{$this->server->branch}/nginx/config");

        // Create Nginx config file

        $result = $this->nginx->generateNginxConfig($nginxPath, 8000, $this->server->branch);
        if ($result) {
            $this->server->logStep("Nginx configuration created at {$nginxPath}");
        } else {
            $this->server->logStep("Failed to create Nginx configuration at {$nginxPath}");
            $this->server->updateStatus('failed');
            return;
        }

    }
}
