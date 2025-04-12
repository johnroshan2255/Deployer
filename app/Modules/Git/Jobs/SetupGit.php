<?php

namespace App\Modules\Git\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Git\Interfaces\GitInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetupGit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DeployedServer $server, private GitInterface $git) {}

    public function handle(): void
    {
        $this->server->logStep("Setting up Git repository...");

        $deploymentPath = base_path("deployments/{$this->server->branch}");
        $gitPath = $deploymentPath . "/app";

        if (!is_dir($deploymentPath)) {
            mkdir($deploymentPath, 0755, true);
        }

        $cloneResult = $this->git->cloneRepository(
            $this->server->repository_url,  // Repository URL from server config
            $gitPath,                       // Target path
            $this->server->branch           // Target branch
        );

        if ($cloneResult['status'] === 'success') {
            $this->server->logStep("Git repository cloned successfully: {$cloneResult['message']}");

            $checkoutResult = $this->git->checkoutBranch($gitPath, $this->server->branch);
            
            if ($checkoutResult['status'] === 'success') {
                $this->server->logStep("Successfully checked out branch: {$this->server->branch}");
                
            } else {
                $this->server->logStep("Failed to checkout branch: {$checkoutResult['message']}");
                $this->server->updateStatus('failed');
                return;
            }
        } else {
            $this->server->logStep("Failed to clone repository: {$cloneResult['message']}");
            
            if (is_dir($gitPath . '/.git')) {
                $this->server->logStep("Repository already exists, attempting to pull latest changes...");
                
                $pullResult = $this->git->pullChanges($gitPath, $this->server->branch);
                
                if ($pullResult['status'] === 'success') {
                    $this->server->logStep("Successfully pulled latest changes: {$pullResult['message']}");
                } else {
                    $this->server->logStep("Failed to pull latest changes: {$pullResult['message']}");
                    $this->server->updateStatus('failed');
                    return;
                }
            } else {
                $this->server->updateStatus('failed');
                return;
            }
        }

        $this->server->logStep("Git repository setup completed successfully.");

    }
}
