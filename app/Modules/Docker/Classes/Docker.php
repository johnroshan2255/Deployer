<?php

namespace App\Modules\Docker\Classes;

class Docker
{
    const APP = 'app';
    const NGINX = 'nginx';
    const MYSQL = 'mysql';
    const REDIS = 'redis';
    const QUEUE = 'queue';

    /**
     * Returns the services in the order they should be initialized
     * 
     * @return array
     */
    public static function ordered(): array
    {
        return [
            self::MYSQL,
            self::REDIS,
            self::APP,
            self::NGINX,
            self::QUEUE
        ];
    }
}