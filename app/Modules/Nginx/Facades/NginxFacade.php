<?php

namespace App\Modules\Nginx\Facades;

use Illuminate\Support\Facades\Facade;

class NginxFacade extends Facade {
    protected static function getFacadeAccessor() {
        return 'nginx-service';
    }
}
