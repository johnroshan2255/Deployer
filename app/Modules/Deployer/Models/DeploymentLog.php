<?php

namespace App\Modules\Deployer\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentLog extends Model
{
    protected $fillable = [
        'deployed_server_id',
        'message',
        'status',
    ];

    public function deployedServer()
    {
        return $this->belongsTo(DeployedServer::class);
    }
}
