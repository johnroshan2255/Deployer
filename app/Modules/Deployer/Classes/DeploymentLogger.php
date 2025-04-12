<?php

namespace App\Modules\Deployer\Classes;

use App\Modules\Deployer\Models\DeployedServer;
use Illuminate\Support\Facades\Log;

class DeploymentLogger
{
    protected DeployedServer $server;

    public function __construct(DeployedServer $server)
    {
        $this->server = $server;
    }

    /**
     * Log a step to database and optionally to a log file
     */
    public function logStep(string $message): void
    {
        // You could store this in a `deployment_logs` table if needed
        Log::info("[Deployment: {$this->server->id}] {$message}");

        $steps = $this->server->steps ?? [];
        $steps[] = [
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ];

        $this->server->update([
            'steps' => $steps
        ]);
    }
}
