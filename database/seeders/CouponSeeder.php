<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Coupon::count() > 0) {
            return;
        }

        $coupons = [
            ['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10, 'min_order_amount' => 150, 'max_discount' => 50, 'usage_limit' => 100, 'used_count' => 0, 'starts_at' => now()->subDay(), 'expires_at' => now()->addMonths(2)],
            ['code' => 'SAVE25', 'type' => 'fixed', 'value' => 25, 'min_order_amount' => 200, 'max_discount' => 25, 'usage_limit' => 80, 'used_count' => 0, 'starts_at' => now()->subDays(2), 'expires_at' => now()->addMonth()],
            ['code' => 'FAMILY15', 'type' => 'percentage', 'value' => 15, 'min_order_amount' => 300, 'max_discount' => 75, 'usage_limit' => 60, 'used_count' => 0, 'starts_at' => now()->subWeek(), 'expires_at' => now()->addWeeks(4)],
            ['code' => 'VIP50', 'type' => 'fixed', 'value' => 50, 'min_order_amount' => 400, 'max_discount' => 50, 'usage_limit' => 40, 'used_count' => 0, 'starts_at' => now()->startOfDay(), 'expires_at' => now()->addMonths(3)],
            ['code' => 'LUNCH20', 'type' => 'percentage', 'value' => 20, 'min_order_amount' => 180, 'max_discount' => 60, 'usage_limit' => 150, 'used_count' => 0, 'starts_at' => now()->subDays(3), 'expires_at' => now()->addMonth()],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
