<?php

namespace App\Modules\Deployer\Facades;

use Illuminate\Support\Facades\Facade;

class DeployerFacade extends Facade {
    protected static function getFacadeAccessor() {
        return 'deployer-service';
    }
}
