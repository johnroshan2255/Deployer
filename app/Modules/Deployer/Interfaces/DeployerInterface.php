<?php

namespace App\Modules\Deployer\Interfaces;

interface DeployerInterface
{
    public function deploy(string $branchName, string $type, string $repositoryUrl): void;
}
