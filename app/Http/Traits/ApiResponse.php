<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Build success response JSON envelope.
     */
    public function success(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'status' => $status,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Build error response JSON envelope.
     */
    public function error(string $message, int $status = 400, mixed $details = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'status' => $status,
            'error' => [
                'message' => $message,
            ],
        ];

        if ($details !== null) {
            $payload['error']['details'] = $details;
        }

        return response()->json($payload, $status, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
