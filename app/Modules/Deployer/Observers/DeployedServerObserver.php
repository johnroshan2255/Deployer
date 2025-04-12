<?php

namespace App\Modules\Deployer\Observers;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Deployer\Jobs\DeployServer;
use App\Modules\Deployer\Interfaces\DeployerInterface;
use Illuminate\Support\Facades\App;

class DeployedServerObserver
{
    public function created(DeployedServer $server): void
    {
        $deployer = App::make(DeployerInterface::class);

        DeployServer::dispatch($server, $deployer);
    }

    public function updated(DeployedServer $server): void
    {
        if ($server->isDirty('status')) {
            
        }
    }

}
