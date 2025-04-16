<?php

namespace App\Modules\Deployer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeployedServer extends Model
{
    protected $fillable = [
        'branch_name',
        'type',
        'status',
        'steps',
        'meta',
    ];

    protected $casts = [
        'steps' => 'array',
        'meta' => 'array',
    ];

    public function deploymentLogs()
    {
        return $this->hasMany(DeploymentLog::class);
    }

    public function logStep(string $message, ? string $status = 'in_progress'): void
    {
        $this->deploymentLogs()->create([
            'message' => Str::limit($message, 2000),
            'status' => $status,
        ]);
    }

    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }
    public function updateSteps(array $steps): void
    {
        $this->update(['steps' => $steps]);
    }
    public function updateBranchName(string $branchName): void
    {
        $this->update(['branch_name' => $branchName]);
    }
    public function updateType(string $type): void
    {
        $this->update(['type' => $type]);
    }
}
