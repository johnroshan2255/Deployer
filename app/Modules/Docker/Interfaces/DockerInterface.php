<?php

namespace App\Modules\Docker\Interfaces;

interface DockerInterface
{
    public function generateDockerComposeFile(string $path, int $basePort = 8000, string $branch = 'main'): array;

    public function startDockerContainers(string $path, string $branch = 'main'): array;
}
