<?php

namespace App\Modules\Nginx\Interfaces;

interface NginxInterface
{
    public function generateNginxConfig(string $path, int $basePort = 8000, string $branch = 'main'): array;

    public function finalizeNginxSetup(string $path, string $branch = 'main'): array;
}
