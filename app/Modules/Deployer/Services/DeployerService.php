<?php

namespace App\Modules\Deployer\Services;

use App\Modules\Deployer\Models\DeployedServer;

class DeployerService
{
    public function createServer(string $branch_name = 'main', string $type = 'api', $repository_url = ''): void
    {
        $server = DeployedServer::where('branch_name', $branch_name)->first();

        if (!isset($server->id)) {
            $server = new DeployedServer();
        }

        $server->branch_name = $branch_name;
        $server->repository_url = $repository_url;
        $server->type = $type;
        $server->status = 'pending';
        $server->steps = [];
        $server->meta = [];
        $server->save();
    }
}
