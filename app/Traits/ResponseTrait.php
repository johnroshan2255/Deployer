<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    protected function formatResponse(bool $status, string $message, array $data = [], int $code = 200, array $extra = []): JsonResponse
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        return response()->json($response, $code);
    }

    protected function successResponse(string $message = "Success", array $data = [], int $code = 200): JsonResponse
    {
        return $this->formatResponse(true, $message, $data, $code);
    }

    protected function errorResponse(string $message = "Something went wrong", int $code = 500, array $extra = []): JsonResponse
    {
        return $this->formatResponse(false, $message, [], $code, $extra);
    }

    protected function validationErrorResponse(array $errors, string $message = "Validation error", int $code = 422): JsonResponse
    {
        return $this->formatResponse(false, $message, [], $code, ['errors' => $errors]);
    }
}
