<?php

namespace App\Modules\Deployer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Deployer\Interfaces\DeployerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeployerController extends Controller
{
    public function __construct(protected DeployerInterface $deployer) {}

    /**
     * Handle the deployment request.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function deploy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branchName' => 'required|string',
            'repositoryUrl' => 'required|string',
            'type' => 'required|string|in:api',
        ]);

        $this->deployer->deploy($data['branchName'], $data['type'], $data['repositoryUrl']);

        return response()->json(['message' => 'Deployment started.']);
    }
}
