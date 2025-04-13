<?php

namespace App\Modules\Docker\Repositories;

use App\Modules\Docker\Interfaces\DockerInterface;
use App\Modules\Docker\Classes\Docker;
use Illuminate\Support\Facades\File;
use App\Modules\Deployer\Traits\DeployTrait;

class DockerRepository implements DockerInterface
{
    use DeployTrait;

    public function generateDockerComposeFile(string $path, int $basePort = 8000, ?string $branch = 'main'): array
    {
        $servicePorts = [
            Docker::APP   => 80,
            Docker::REDIS => 6379,
            Docker::MYSQL => 3306,
            Docker::QUEUE => 6001,
            Docker::NGINX => 8080,
        ];

        $yaml = "version: '3.8'\nservices:\n";
        $usedPorts = [];

        $instance = new self();

        foreach (Docker::ordered() as $service) {
            $defaultPort = $servicePorts[$service] ?? null;
            if ($defaultPort === null) continue;

            $availablePort = $instance->getAvailablePort($basePort, $basePort + 1000);
            if (!$availablePort) {
                return [
                    'success' => false,
                    'error' => "No available port found for service: {$service}"
                ];
            }

            $usedPorts[$service] = $availablePort;
            $yaml .= self::buildServiceBlock($service, $availablePort, $branch);
        }

        $yaml .= "\nvolumes:\n  mysql-data:\n\nnetworks:\n  laravel:\n    driver: bridge\n";

        File::ensureDirectoryExists($path);
        File::put("{$path}/docker-compose.yml", $yaml);

        return [
            'success' => true,
            'error' => "Docker compose file created at {$path}/docker-compose.yml",
            'ports' => $usedPorts,
            'dockerPath' => "{$path}/docker-compose.yml"
        ];
    }

    protected function buildServiceBlock(string $service, int $port, string $branch): string
    {
        $appPath = "./deployments/{$branch}/app";
        $nginxConf = "./deployments/{$branch}/nginx/config/default.conf";

        $indent = '    ';
        $block = "  {$service}:\n";

        switch ($service) {
            case Docker::APP:
                $block .= "{$indent}image: laravel-app:latest\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:80\"\n";
                $block .= "{$indent}volumes:\n";
                $block .= "{$indent}- {$appPath}:/var/www/html\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;

            case Docker::NGINX:
                $block .= "{$indent}image: nginx:latest\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:80\"\n";
                $block .= "{$indent}volumes:\n";
                $block .= "{$indent}- {$nginxConf}:/etc/nginx/conf.d/default.conf\n";
                $block .= "{$indent}- {$appPath}:/var/www/html\n";
                $block .= "{$indent}depends_on:\n";
                $block .= "{$indent}- app\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;

            case Docker::MYSQL:
                $block .= "{$indent}image: mysql:8.0\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:3306\"\n";
                $block .= "{$indent}environment:\n";
                $block .= "{$indent}  MYSQL_ROOT_PASSWORD: root\n";
                $block .= "{$indent}  MYSQL_DATABASE: laravel\n";
                $block .= "{$indent}  MYSQL_USER: user\n";
                $block .= "{$indent}  MYSQL_PASSWORD: secret\n";
                $block .= "{$indent}volumes:\n";
                $block .= "{$indent}- mysql-data:/var/lib/mysql\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;

            case Docker::REDIS:
                $block .= "{$indent}image: redis:alpine\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:6379\"\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;

            case Docker::QUEUE:
                $block .= "{$indent}image: laravel-worker:latest\n";
                $block .= "{$indent}depends_on:\n";
                $block .= "{$indent}- app\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:6001\"\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;
        }

        $block .= "\n";
        return $block;
    }

    public function startDockerContainers(string $path, ?string $branch = 'main'): array
    {
        $command = "docker-compose -f {$path}/docker-compose.yml up -d";
        $output = [];
        $returnVar = null;

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return [
                'success' => false,
                'error' => "Failed to start Docker containers: " . implode("\n", $output)
            ];
        }
        return [
            'success' => true,
            'error' => "Docker containers started successfully.",
            'dockerPath' => $path
        ];
    }
}
