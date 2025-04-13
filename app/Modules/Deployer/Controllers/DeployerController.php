<?php

namespace App\Modules\Deployer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Deployer\Interfaces\DeployerInterface;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeployerController extends Controller
{
    use ResponseTrait;

    public function __construct(protected DeployerInterface $deployer) {}

    /**
     * Handle the deployment request.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function deploy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branchName' => 'required|string',
            'repositoryUrl' => 'required|string',
            'type' => 'required|string|in:api',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $this->deployer->deploy($data['branchName'], $data['type'], $data['repositoryUrl']);

        return response()->json(['message' => 'Deployment started.']);
    }
}
