<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for malformed JSON payload if raw input exists
        $raw = $request->getContent();
        if (!empty($raw) && ($request->isJson() || str_starts_with($raw, '{') || str_starts_with($raw, '['))) {
            json_decode($raw);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'status' => 422,
                    'error' => [
                        'message' => 'Malformed or corrupted JSON payload: ' . json_last_error_msg(),
                    ],
                ], 422, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        // Clean null bytes and sanitize request inputs
        $inputs = $request->all();
        if (!empty($inputs)) {
            $cleaned = $this->cleanRecursive($inputs);
            $request->merge($cleaned);
        }

        return $next($request);
    }

    /**
     * Recursively strip null bytes and trim strings while preserving multi-byte unicode.
     */
    private function cleanRecursive(mixed $data): mixed
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $val) {
                $cleanKey = is_string($key) ? str_replace(chr(0), '', $key) : $key;
                $cleaned[$cleanKey] = $this->cleanRecursive($val);
            }
            return $cleaned;
        }

        if (is_string($data)) {
            return str_replace(chr(0), '', $data);
        }

        return $data;
    }
}
