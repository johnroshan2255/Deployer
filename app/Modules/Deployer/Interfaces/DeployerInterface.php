<?php

namespace App\Modules\Deployer\Interfaces;

use App\Modules\Deployer\Models\DeployedServer;

interface DeployerInterface
{
    public function deploy(string $branchName, string $type, string $repositoryUrl): void;

    public static function prepareDeploymentDirectory(DeployedServer $server): bool;
}
