<?php

namespace App\Modules\Deployer\Observers;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Deployer\Jobs\DeployServer;
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
        DeployServer::dispatch($server);
    }
}
