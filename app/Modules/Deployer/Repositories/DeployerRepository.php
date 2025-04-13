<?php

namespace App\Modules\Deployer\Repositories;

use App\Modules\Deployer\Interfaces\DeployerInterface;
use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Deployer\Services\DeployerService;
use Illuminate\Support\Facades\File;

class DeployerRepository implements DeployerInterface
{
    /**
     * Start deployment by dispatching a job.
     *
     * @param  string  $branchName
     * @param  string  $type
     * @param  string  $repositoryUrl
     * @return void
     */
    public function deploy(string $branchName, string $type, string $repositoryUrl): void
    {
        $service = new DeployerService();
        $service->createServer($branchName, $type, $repositoryUrl);
    }

    /**
     * Prepare the directory structure for deployment.
     *
     * @param DeployedServer $server
     * @return bool
     */

    public static function prepareDeploymentDirectory(DeployedServer $server): bool
    {
        try {
            $basePath = base_path("deployments/{$server->branch_name}");

            // Attempt to create directories
            File::ensureDirectoryExists("{$basePath}/app");
            File::ensureDirectoryExists("{$basePath}/nginx");
            File::ensureDirectoryExists("{$basePath}/nginx/logs");
            File::ensureDirectoryExists("{$basePath}/docker");

            $logDirectory = "{$basePath}/nginx/logs";

            if (!File::exists("{$logDirectory}/error.log")) {
                File::put("{$logDirectory}/error.log", '');
            }

            if (!File::exists("{$logDirectory}/access.log")) {
                File::put("{$logDirectory}/access.log", '');
            }

            $server->update([
                'meta' => array_merge($server->meta ?? [], [
                    'deployment_path' => "deployments/{$server->branch_name}"
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
