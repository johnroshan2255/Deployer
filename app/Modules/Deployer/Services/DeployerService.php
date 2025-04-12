<?php

namespace App\Modules\Deployer\Services;

use App\Modules\Deployer\Models\DeployedServer;

class DeployerService
{
    public function createServer(string $branch_name = 'main', string $type = 'api', $repository_url = ''): void
    {
        $server = new DeployedServer();
        $server->branch_name = $branch_name;
        $server->repository_url = '';
        $server->type = $type;
        $server->status = 'pending';
        $server->steps = '{}';
        $server->meta = '{}';
        $server->save();
    }
}
