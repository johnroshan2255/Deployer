<?php

namespace App\Modules\Docker\Classes;

class Docker
{
    public const APP = 'app';
    public const REDIS = 'redis';
    public const MYSQL = 'mysql';
    public const NGINX = 'nginx';
    public const QUEUE = 'queue';

    /**
     * List of services in deployment order.
     *
     * @return array
     */
    public static function ordered(): array
    {
        return [
            self::APP,
            self::REDIS,
            self::MYSQL,
            self::QUEUE,
            self::NGINX,
        ];
    }

    public const SERVICES = [
        'app',
        'redis',
        'mysql'
    ];
}
