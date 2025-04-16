<?php

namespace App\Modules\Docker\Facades;

use Illuminate\Support\Facades\Facade;

class DockerFacade extends Facade {
    protected static function getFacadeAccessor() {
        return 'docker-service';
    }
}
