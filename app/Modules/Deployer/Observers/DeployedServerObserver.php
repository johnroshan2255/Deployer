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
        self::init($server);
    }

    public function saved(DeployedServer $server): void
    {
        if ($server->isDirty('status')) {
        }

        // if (!$server->isDirty('status') && $server->status == 'pending') {
        //     self::init($server);
        // }
    }

    private static function init($server)
    {
        $deployer = App::make(DeployerInterface::class);

        DeployServer::dispatch($server, $deployer);
    }
}
