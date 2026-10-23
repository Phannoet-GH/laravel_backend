<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    use ApiResponse;

    public function health(): JsonResponse
    {
        return $this->success([
            'status' => 'online',
            'app' => 'LuxeCart SE Shop REST API (Laravel 13)',
            'security' => [
                'xss_protection' => 'Sanitizer + HTML Entity Encoding + CSP',
                'csrf_protection' => 'Sanctum / Anti-CSRF Token Verification',
                'sql_injection_protection' => 'Eloquent & PDO Prepared Statements with Param Binding',
                'password_hashing' => 'Bcrypt / PASSWORD_DEFAULT',
            ],
            'framework' => 'Laravel 13 (PHP 8.3)',
            'timestamp' => now()->toISOString(),
            'endpoints' => [
                'GET /api/csrf-token' => 'Obtain anti-CSRF token for state-changing operations',
                'GET /api/products' => 'List products with optional category, search, or id',
                'GET /api/categories' => 'List all categories',
                'POST /api/auth/login' => 'User login',
                'POST /api/auth/register' => 'User registration',
                'POST /api/auth/forgot-password' => 'Send password reset OTP code',
                'POST /api/auth/verify-otp' => 'Verify password reset OTP code',
                'POST /api/auth/reset-password' => 'Reset password with OTP',
                'GET /api/orders' => 'List orders',
                'POST /api/orders' => 'Place new order',
                'GET /api/orders/track' => 'Track order status by tracking number',
                'GET /api/reviews' => 'Get product reviews',
                'POST /api/reviews' => 'Submit product review',
                'POST /api/coupons/validate' => 'Validate voucher discount',
            ],
        ]);
    }

    public function csrfToken(): JsonResponse
    {
        return $this->success([
            'csrf_token' => bin2hex(random_bytes(32)),
            'expires_in' => 86400,
        ]);
    }
}
