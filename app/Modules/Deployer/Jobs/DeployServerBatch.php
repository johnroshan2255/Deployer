<?php

namespace App\Modules\Deployer\Jobs;

use App\Modules\Deployer\Models\DeployedServer;
use App\Modules\Docker\Interfaces\DockerInterface;
use App\Modules\Docker\Jobs\CreateDockerYmlFile;
use App\Modules\Docker\Jobs\StartDockerContainers;
use App\Modules\Git\Interfaces\GitInterface;
use App\Modules\Git\Jobs\SetupGit;
use App\Modules\Nginx\Interfaces\NginxInterface;
use App\Modules\Nginx\Jobs\CreateNginxConfig;
use App\Modules\Nginx\Jobs\FinalizeNginxSetup;
use Illuminate\Support\Facades\App;

class DeployServerBatch
{
    public static function dispatch(DeployedServer $server): void
    {
        $docker = App::make(DockerInterface::class);
        $nginx = App::make(NginxInterface::class);
        $git = App::make(GitInterface::class);

        $server->logStep("Dispatching deployment batch...");

        CreateDockerYmlFile::withChain([
            new SetupGit($server, $git),
            new CreateNginxConfig($server, $nginx),
            new FinalizeNginxSetup($server, $nginx),
            new StartDockerContainers($server, $docker),
        ])
        ->dispatch($server, $docker);
    }
}

