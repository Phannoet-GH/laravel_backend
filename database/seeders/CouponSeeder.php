<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'LUXE20', 'discount_type' => 'percent', 'discount_value' => 20.0, 'min_order_amount' => 50.0, 'is_active' => true],
            ['code' => 'SAVE10', 'discount_type' => 'fixed', 'discount_value' => 10.0, 'min_order_amount' => 60.0, 'is_active' => true],
            ['code' => 'FREESHIP', 'discount_type' => 'freeship', 'discount_value' => 12.0, 'min_order_amount' => 80.0, 'is_active' => true],
            ['code' => 'VIP50', 'discount_type' => 'percent', 'discount_value' => 50.0, 'min_order_amount' => 200.0, 'is_active' => true],
        ];

        foreach ($coupons as $coup) {
            Coupon::updateOrCreate(['code' => $coup['code']], $coup);
        }
    }
}
