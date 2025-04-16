<?php

namespace App\Modules\Deployer\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Docker\Jobs\CreateDockerYmlFile;
use App\Modules\Docker\Jobs\SetupApplication;
use App\Modules\Docker\Jobs\StartDockerContainers;
use App\Modules\Git\Jobs\SetupGit;
use App\Modules\Nginx\Jobs\CreateNginxConfig;
use App\Modules\Nginx\Jobs\FinalizeNginxSetup;

class DeployServerBatch
{
    public static function dispatch(DeployedServer $server): void
    {
        $server->logStep("Dispatching deployment batch...");

        CreateDockerYmlFile::withChain([
            new SetupGit($server),
            new CreateNginxConfig($server),
            new FinalizeNginxSetup($server),
            new StartDockerContainers($server),
            new SetupApplication($server),
        ])
            ->dispatch($server);
    }
}
