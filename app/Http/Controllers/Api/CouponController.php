<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Coupon;
use App\Security\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ApiResponse;

    public function validateCoupon(Request $request): JsonResponse
    {
        $rawCode = $request->input('code');
        if (empty($rawCode)) {
            return $this->error('Coupon code is required.', 422);
        }

        $code = Sanitizer::cleanString($rawCode, 50);
        $subtotal = Sanitizer::cleanFloat($request->input('subtotal', 0.0));

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return $this->error('Invalid coupon code. Try LUXE20, SAVE10, or FREESHIP.', 404);
        }

        if ($subtotal < (float) $coupon->min_order_amount) {
            return $this->error("Minimum order amount of \${$coupon->min_order_amount} required for code '{$coupon->code}'.", 422);
        }

        $discountAmount = 0.0;
        $val = (float) $coupon->discount_value;

        if ($coupon->discount_type === 'percent') {
            $discountAmount = $subtotal * ($val / 100.0);
        } elseif ($coupon->discount_type === 'fixed') {
            $discountAmount = min($subtotal, $val);
        } elseif ($coupon->discount_type === 'freeship') {
            $discountAmount = 0.0;
        }

        return $this->success([
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $val,
            'discount_amount' => round($discountAmount, 2),
            'is_free_shipping' => $coupon->discount_type === 'freeship',
        ]);
    }
}
