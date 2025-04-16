<?php

namespace App\Modules\Git\Facades;

use Illuminate\Support\Facades\Facade;

class GitFacade extends Facade {
    protected static function getFacadeAccessor() {
        return 'git-service';
    }
}
