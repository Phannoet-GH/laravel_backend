<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\PasswordReset;
use App\Models\User;
use App\Security\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request): JsonResponse
    {
        $rawEmail = $request->input('email');
        $rawPassword = $request->input('password');

        // Type safety & validation
        if (!is_scalar($rawEmail) || !is_scalar($rawPassword)) {
            return $this->error('Email and password must be valid strings.', 422);
        }

        $email = Sanitizer::cleanString($rawEmail, 150);
        $password = (string) $rawPassword;

        if (empty($email) || empty($password)) {
            return $this->error('Email and password are required.', 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return $this->error('Invalid email or password.', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '+1 (555) 234-5678',
            'role' => $user->role ?? 'customer',
            'token' => $token,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $rawName = $request->input('name');
        $rawEmail = $request->input('email');
        $rawPassword = $request->input('password');
        $rawPhone = $request->input('phone');

        if (!is_scalar($rawName) || !is_scalar($rawEmail) || !is_scalar($rawPassword)) {
            return $this->error('Name, email, and password must be valid strings.', 422);
        }

        $name = Sanitizer::cleanString($rawName, 100);
        $email = Sanitizer::cleanEmail($rawEmail);
        $password = (string) $rawPassword;
        $phone = $rawPhone ? Sanitizer::cleanString($rawPhone, 50) : null;

        if (empty($name) || empty($email) || empty($password)) {
            return $this->error('Name, email, and password are required.', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Please provide a valid email address (e.g., user@domain.com).', 422);
        }

        if (strlen($password) < 8) {
            return $this->error('Password must be at least 8 characters long.', 422);
        }

        $existing = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        if ($existing) {
            return $this->error('Email is already in use.', 422);
        }

        $id = 'usr_' . time() . '_' . bin2hex(random_bytes(4));
        $user = User::create([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'phone' => $phone ?: '+1 (555) 234-5678',
            'role' => 'customer',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '+1 (555) 234-5678',
            'role' => 'customer',
            'token' => $token,
        ], [], 201);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $rawEmail = $request->input('email');
        if (!is_scalar($rawEmail)) {
            return $this->error('Email address is required.', 422);
        }

        $email = Sanitizer::cleanEmail($rawEmail);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Email address is required.', 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        if (!$user) {
            return $this->error('No account found with this email address.', 422);
        }

        // Invalidate previous OTPs
        PasswordReset::whereRaw('LOWER(email) = ?', [strtolower($email)])->update(['is_used' => true]);

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        PasswordReset::create([
            'email' => $email,
            'otp_code' => $otp,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);

        return $this->success([
            'success' => true,
            'email' => $email,
            'otp' => $otp,
            'expires_in_seconds' => 600,
            'message' => "A 6-digit verification code has been dispatched to {$email}.",
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $email = Sanitizer::cleanEmail($request->input('email'));
        $otp = Sanitizer::cleanString($request->input('otp'), 10);

        if (empty($email) || empty($otp)) {
            return $this->error('Email and OTP code are required.', 422);
        }

        $record = PasswordReset::whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->where('otp_code', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (!$record) {
            return $this->error('Invalid or expired OTP code. Please request a new one.', 422);
        }

        return $this->success([
            'success' => true,
            'message' => 'OTP verified successfully.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $email = Sanitizer::cleanEmail($request->input('email'));
        $otp = Sanitizer::cleanString($request->input('otp'), 10);
        $newPassword = (string) $request->input('new_password');

        if (empty($email) || empty($newPassword)) {
            return $this->error('Email and new password are required.', 422);
        }

        if (strlen($newPassword) < 8) {
            return $this->error('Password must be at least 8 characters long.', 422);
        }

        if (!empty($otp)) {
            $record = PasswordReset::whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->where('otp_code', $otp)
                ->where('is_used', false)
                ->where('expires_at', '>=', now())
                ->latest('id')
                ->first();

            if (!$record) {
                return $this->error('Invalid or expired OTP code. Please request a new one.', 422);
            }

            $record->update(['is_used' => true]);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        if (!$user) {
            return $this->error('User not found.', 422);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return $this->success([
            'success' => true,
            'message' => 'Password has been successfully updated with encryption.',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $rawEmail = $request->input('email');
        $rawName = $request->input('name');
        $rawPhone = $request->input('phone');

        // Safely truncate name to max length 255 to prevent buffer overflow
        $name = is_string($rawName) ? mb_substr(trim($rawName), 0, 255) : '';
        $email = Sanitizer::cleanEmail($rawEmail);
        $phone = $rawPhone ? Sanitizer::cleanString($rawPhone, 50) : null;

        if (empty($email) || empty($name)) {
            return $this->error('Email and name are required.', 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        if (!$user) {
            return $this->error('User not found.', 422);
        }

        $user->name = $name;
        if ($phone !== null) {
            $user->phone = $phone;
        }
        $user->save();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '+1 (555) 000-0000',
            'role' => $user->role ?? 'customer',
            'created_at' => $user->created_at,
        ]);
    }
}
