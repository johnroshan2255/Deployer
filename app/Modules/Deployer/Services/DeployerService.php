<?php

namespace App\Modules\Deployer\Services;

use App\Modules\Deployer\Models\DeployedServer;
use Illuminate\Support\Facades\File;

class DeployerService
{
    /**
     * Prepare the directory structure for deployment.
     *
     * @param DeployedServer $server
     * @return bool
     */

    public static function prepareDeploymentDirectory(DeployedServer $server): bool
    {
        try {
            $branch = $server->branch_name;
            $basePath = base_path("deployments/{$branch}");

            File::ensureDirectoryExists("{$basePath}/app");
            File::ensureDirectoryExists("{$basePath}/nginx/config");
            File::ensureDirectoryExists("{$basePath}/nginx/logs");
            File::ensureDirectoryExists("{$basePath}/docker");

            $logDirectory = "{$basePath}/nginx/logs";

            if (!File::exists("{$logDirectory}/error.log")) {
                File::put("{$logDirectory}/error.log", '');
            }

            if (!File::exists("{$logDirectory}/access.log")) {
                File::put("{$logDirectory}/access.log", '');
            }

            $hostsFilePath = "{$basePath}/nginx/config/hosts";
            $hostEntry = "127.0.0.1 {$branch}.api.com";

            File::put($hostsFilePath, $hostEntry . PHP_EOL);

            $server->update([
                'meta' => array_merge($server->meta ?? [], [
                    'deployment_path' => "deployments/{$branch}"
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

}
