<?php

namespace App\Modules\Docker\Services;

use App\Modules\Docker\Classes\Docker;
use Illuminate\Support\Facades\File;
use App\Modules\Deployer\Traits\DeployTrait;
use Symfony\Component\Process\Process;

class DockerService
{
    use DeployTrait;
    
    const DOCKERFILE_APP = 'Dockerfile.app';
    const DOCKERFILE_WORKER = 'Dockerfile.worker';

    public function generateDockerComposeFile(string $path, int $basePort = 8000, ?string $branch = 'main'): array
    {
        $servicePorts = [
            Docker::APP   => 9000,
            Docker::REDIS => 6379,
            Docker::MYSQL => 3306,
            Docker::QUEUE => 6001,
            Docker::NGINX => 8080,
        ];

        $yaml = "version: '3.8'\nservices:\n";
        $usedHostPorts = [];
        $usedPorts = [];

        foreach (Docker::ordered() as $service) {
            $containerPort = $servicePorts[$service] ?? null;
            if ($containerPort === null) continue;

            $hostPort = $basePort;
            while (in_array($hostPort, $usedHostPorts)) {
                $hostPort++;
                if ($hostPort > $basePort + 1000) {
                    return [
                        'success' => false,
                        'message' => "No available port found for {$service} in range {$basePort}-" . ($basePort + 1000)
                    ];
                }
            }

            $usedHostPorts[] = $hostPort;
            $usedPorts[$service] = $hostPort;
            $yaml .= self::buildServiceBlock($service, $hostPort, $branch);
        }

        $yaml .= "\nvolumes:\n  mysql-data:\n\nnetworks:\n  laravel:\n    driver: bridge\n";

        File::ensureDirectoryExists($path);
        File::put("{$path}/docker-compose.yml", $yaml);

        return [
            'success' => true,
            'message' => "Docker compose file created at {$path}/docker-compose.yml",
            'ports' => $usedPorts,
            'dockerPath' => "{$path}/docker-compose.yml"
        ];
    }

    protected function buildServiceBlock(string $service, int $port, string $branch): string
    {
        $appPath = "../../../deployments/{$branch}/app";
        $nginxConf = "../../../deployments/{$branch}/nginx/config/default.conf";

        $indent = '    ';
        $block = "  {$service}:\n";

        switch ($service) {
            case Docker::APP:
                $block .= "{$indent}build:\n";
                $block .= "{$indent}  context: ./docker\n";
                $block .= "{$indent}  dockerfile: Dockerfile.app\n";
                $block .= "{$indent}container_name: laravel-app-{$branch}\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:9000\"\n";
                $block .= "{$indent}volumes:\n";
                $block .= "{$indent}- {$appPath}:/var/www/html\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;

            case Docker::NGINX:
                $nginxLogs = "../../../deployments/{$branch}/nginx/logs";
                $accessLog = "{$nginxLogs}/access.log";
                $errorLog = "{$nginxLogs}/error.log";
                $nginxHosts = "../../../deployments/{$branch}/nginx/config/hosts";
                
                // // Ensure log directory and files exist
                // if (!file_exists($nginxLogs)) {
                //     mkdir($nginxLogs, 0755, true);
                // }
                
                // // Touch log files to ensure they exist
                // if (!file_exists($accessLog)) {
                //     touch($accessLog);
                // }
                
                // if (!file_exists($errorLog)) {
                //     touch($errorLog);
                // }
                
                $block .= "{$indent}image: nginx:stable-alpine\n";
                $block .= "{$indent}container_name: nginx-{$branch}\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:80\"\n";
                $block .= "{$indent}volumes:\n";
                $block .= "{$indent}- {$nginxConf}:/etc/nginx/conf.d/default.conf\n";
                $block .= "{$indent}- {$appPath}:/var/www/html\n";
                $block .= "{$indent}- {$accessLog}:/var/log/nginx/access.log\n";
                $block .= "{$indent}- {$errorLog}:/var/log/nginx/error.log\n";
                $block .= "{$indent}- {$nginxHosts}:/etc/hosts\n";
                $block .= "{$indent}depends_on:\n";
                $block .= "{$indent}- app\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;

            case Docker::MYSQL:
                $block .= "{$indent}image: mysql:8.0\n";
                $block .= "{$indent}container_name: mysql-{$branch}\n";
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
                $block .= "{$indent}container_name: redis-{$branch}\n";
                $block .= "{$indent}ports:\n";
                $block .= "{$indent}- \"{$port}:6379\"\n";
                $block .= "{$indent}networks:\n";
                $block .= "{$indent}- laravel\n";
                break;

            case Docker::QUEUE:
                $block .= "{$indent}build:\n";
                $block .= "{$indent}  context: ./docker\n";
                $block .= "{$indent}  dockerfile: Dockerfile.worker\n";
                $block .= "{$indent}container_name: laravel-worker-{$branch}\n";
                $block .= "{$indent}volumes:\n";
                $block .= "{$indent}- {$appPath}:/var/www/html\n";
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

    public function createDockerfiles(string $path): array
    {
        File::ensureDirectoryExists("{$path}/docker");
        
        // Create Dockerfile for Laravel App with PHP-FPM
        $dockerfileApp = <<<DOCKERFILE
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Add user for application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Change current user to www
USER www

EXPOSE 9000
CMD ["php-fpm"]
DOCKERFILE;

        // Create Dockerfile for Laravel Queue Worker
        $dockerfileWorker = <<<DOCKERFILE
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Add user for application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Change current user to www
USER www

CMD ["php", "artisan", "queue:work", "--tries=3", "--timeout=90"]
DOCKERFILE;

        File::put("{$path}/docker/".self::DOCKERFILE_APP, $dockerfileApp);
        File::put("{$path}/docker/".self::DOCKERFILE_WORKER, $dockerfileWorker);

        return [
            'success' => true,
            'message' => "Dockerfiles created at {$path}/docker/",
            'dockerfiles' => [
                'app' => "{$path}/docker/".self::DOCKERFILE_APP,
                'worker' => "{$path}/docker/".self::DOCKERFILE_WORKER
            ]
        ];
    }

    public function buildDockerImages(string $path, ?string $branch = 'main'): array
    {
        $dockerDir = "{$path}/docker";
        
        if (!file_exists("{$dockerDir}/".self::DOCKERFILE_APP) || !file_exists("{$dockerDir}/".self::DOCKERFILE_WORKER)) {
            $this->createDockerfiles($path);
        }
        
        $images = [
            'app' => "laravel-app-{$branch}",
            'worker' => "laravel-worker-{$branch}"
        ];
        
        $results = [];
        
        foreach ($images as $type => $imageName) {
            $dockerfile = $type === 'app' ? self::DOCKERFILE_APP : self::DOCKERFILE_WORKER;
            
            $buildProcess = new Process([
                'docker', 'build', 
                '-t', $imageName, 
                '-f', "{$dockerDir}/{$dockerfile}", 
                $dockerDir
            ]);
            
            $buildProcess->setTimeout(300); // 5 minutes
            $buildProcess->run();
            
            if (!$buildProcess->isSuccessful()) {
                return [
                    'success' => false,
                    'message' => "Failed to build {$type} image: " . $buildProcess->getErrorOutput()
                ];
            }
            
            $results[$type] = [
                'image' => $imageName,
                'output' => $buildProcess->getOutput()
            ];
        }
        
        return [
            'success' => true,
            'message' => "Docker images built successfully",
            'images' => $results
        ];
    }

    public function startDockerContainers(string $path, ?string $branch = 'main'): array
    {
        $isWsl = (stripos(php_uname('r'), 'microsoft') !== false || stripos(php_uname('r'), 'wsl') !== false);
        $isLinux = (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN');
        
        $environment = $isWsl ? 'WSL' : ($isLinux ? 'Linux' : 'Windows');
        
        if ($isLinux || $isWsl) {
            $permissions = new Process(['chmod', '-R', '755', $path]);
            $permissions->run();
            
            if ($isWsl) {
                $fixLineEndings = new Process(['find', $path, '-type', 'f', '-name', '*.sh', '-exec', 'dos2unix', '{}', ';']);
                $fixLineEndings->run();
            }
        } else {
            return [
                'success' => false,
                'message' => "You must need a linux server to run this.",
                'environment' => $environment
            ];
        }
        
        $checkImagesProcess = new Process(['docker', 'images', '--format', '{{.Repository}}']);
        $checkImagesProcess->setTimeout(120);
        $checkImagesProcess->run();
        
        if (!$checkImagesProcess->isSuccessful()) {
            return [
                'success' => false,
                'message' => "Failed to check Docker images: " . $checkImagesProcess->getErrorOutput(),
                'environment' => $environment
            ];
        }
        
        $existingImages = explode("\n", $checkImagesProcess->getOutput());
        
        $requiredImages = [
            "laravel-app-{$branch}",
            "laravel-worker-{$branch}"
        ];
        
        $missingImages = array_filter($requiredImages, function($image) use ($existingImages) {
            return !in_array($image, $existingImages);
        });
        
        if (!empty($missingImages)) {
            $buildResult = $this->buildDockerImages($path, $branch);
            if (!$buildResult['success']) {
                return $buildResult;
            }
        }
        
        $composeFile = "{$path}/docker-compose.yml";
        
        if (!file_exists($composeFile)) {
            return [
                'success' => false,
                'message' => "Docker compose file not found at {$composeFile}",
                'environment' => $environment
            ];
        }
        
        $composeCommand = [];
        
        if ($isWsl || $isLinux) {
            $checkComposeV2 = new Process(['docker', 'compose', 'version']);
            $checkComposeV2->run();
            
            if ($checkComposeV2->isSuccessful()) {
                $composeCommand = ['docker', 'compose'];
            } else {
                $composeCommand = ['docker-compose'];
            }
        } else {
            // Windows native
            $composeCommand = ['docker-compose'];
        }
        
        $commandArray = array_merge($composeCommand, ['-f', $composeFile, 'up', '-d']);
        
        $startProcess = new Process($commandArray);
        $startProcess->setTimeout(300); // 5 minutes
        $startProcess->run();
        
        if (!$startProcess->isSuccessful()) {
            return [
                'success' => false,
                'message' => "Failed to start Docker containers: " . $startProcess->getErrorOutput(),
                'command' => implode(' ', $commandArray),
                'environment' => $environment
            ];
        }
        
        $ps = new Process(['docker', 'ps', '--format', '{{.Names}}', '--filter', "name=*-{$branch}"]);
        $ps->setTimeout(60);
        $ps->run();
        
        if (!$ps->isSuccessful()) {
            return [
                'success' => true,
                'message' => "Containers started but verification failed: " . $ps->getErrorOutput(),
                'environment' => $environment
            ];
        }
        
        $runningContainers = array_filter(explode("\n", trim($ps->getOutput())));
        
        return [
            'success' => true,
            'message' => "Docker containers started successfully in {$environment} environment.",
            'dockerPath' => $path,
            'environment' => $environment,
            'runningContainers' => $runningContainers
        ];
    }

    public function setupLaravelApp(string $branch = 'main'): array
    {
        $containerName = "laravel-app-{$branch}";

        // Composer install
        $composerCmd = ['docker', 'exec', $containerName, 'bash', '-c', 'cd /var/www/html && composer install --no-dev --optimize-autoloader'];
        $composer = new Process($composerCmd);
        $composer->setTimeout(300);
        $composer->run();

        if (!$composer->isSuccessful()) {
            return [
                'success' => false,
                'message' => 'Composer install failed: ' . $composer->getErrorOutput(),
            ];
        }

        // Check if .env exists
        $checkEnv = new Process(['docker', 'exec', $containerName, 'bash', '-c', 'cd /var/www/html && test -f .env']);
        $checkEnv->run();
        if (!$checkEnv->isSuccessful()) {
            $copyEnv = new Process(['docker', 'exec', $containerName, 'bash', '-c', 'cd /var/www/html && cp .env.example .env']);
            $copyEnv->run();
        }

        // Key generate
        $keyGen = new Process(['docker', 'exec', $containerName, 'bash', '-c', 'cd /var/www/html && php artisan key:generate']);
        $keyGen->run();
        if (!$keyGen->isSuccessful()) {
            return [
                'success' => false,
                'message' => 'Key generation failed: ' . $keyGen->getErrorOutput(),
            ];
        }

        // Permissions
        $permissions = new Process(['docker', 'exec', $containerName, 'bash', '-c', 'cd /var/www/html && chmod -R 775 storage bootstrap/cache']);
        $permissions->run();

        return [
            'success' => true,
            'message' => 'Laravel app setup completed successfully.',
        ];
    }

}