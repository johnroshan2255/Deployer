<?php

namespace App\Modules\Deployer\Repositories;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Deployer\Services\DeployerService;
use Illuminate\Support\Facades\Storage;

class DeployerRepository
{
    /**
     * Start deployment by dispatching a job.
     *
     * @param  string  $branchName
     * @param  string  $type
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
            $basePath = "deployments/{$server->branch_name}";

            // Attempt to create directories
            Storage::makeDirectory("{$basePath}/app");
            Storage::makeDirectory("{$basePath}/nginx");
            Storage::makeDirectory("{$basePath}/docker");

            // Optionally store base path in meta
            $server->update([
                'meta' => array_merge($server->meta ?? [], [
                    'deployment_path' => $basePath
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}