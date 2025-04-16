<?php

namespace App\Modules\Deployer\Repositories;

use App\Modules\Deployer\Interfaces\DeployerInterface;
use App\Modules\Deployer\Models\DeployedServer;

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
        $server = DeployedServer::where('branch_name', $branchName)->first();

        if (!isset($server->id)) {
            $server = new DeployedServer();
        }

        $server->branch_name = $branchName;
        $server->repository_url = $repositoryUrl;
        $server->type = $type;
        $server->status = 'pending';
        $server->steps = [];
        $server->meta = [];
        $server->save();
    
    }
}
