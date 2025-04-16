<?php

namespace App\Modules\Nginx\Services;

use Illuminate\Support\Facades\File;

class NginxService
{
    public function generateNginxConfig(string $path, int $basePort = 8000, ?string $branch = 'main'): array
    {
        $serverName = "{$branch}.api.com";

        $config = <<<NGINX
server {
    listen 80;
    server_name {$serverName};

    root /var/www/html/public;
    index index.php index.html;

    access_log /var/log/nginx/{$branch}_access.log;
    error_log /var/log/nginx/{$branch}_error.log;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        access_log off;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX;

        File::ensureDirectoryExists($path);
        $confPath = "{$path}/default.conf";
        File::put($confPath, $config);

        return [
            'success' => true,
            'message' => "Nginx config created at {$confPath}",
            'path' => $confPath,
            'domain' => $serverName,
        ];
    }

    public function finalizeNginxSetup(string $path, ?string $branch = 'main'): array
    {
        $nginxConfPath = "{$path}/default.conf";

        // 1. Check if the config file exists
        if (!file_exists($nginxConfPath)) {
            return [
                'success' => false,
                'message' => "Nginx config file not found at {$nginxConfPath}",
            ];
        }

        // 2. Validate contents (basic check for expected server_name and fastcgi_pass)
        $content = file_get_contents($nginxConfPath);
        $expectedServerName = "{$branch}.api.com";

        if (!str_contains($content, $expectedServerName)) {
            return [
                'success' => false,
                'message' => "Nginx config does not contain expected server_name: {$expectedServerName}",
            ];
        }

        if (!str_contains($content, 'fastcgi_pass app:9000')) {
            return [
                'success' => false,
                'message' => "Nginx config does not have correct fastcgi_pass to 'app:9000'",
            ];
        }

        return [
            'success' => true,
            'message' => "Nginx configuration is present and looks good.",
            'path' => $nginxConfPath,
        ];
    }
}