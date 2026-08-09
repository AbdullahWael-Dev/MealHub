<?php

namespace App\Services\V1\CouponServices;

use App\Models\Coupon;
use Exception;

class CouponService
{
    public function validateCoupon(string $code, float $subtotal): Coupon
    {
        $coupon = Coupon::byCode($code)->first();
        if (!$coupon) {
            throw new Exception('Coupon code not found.');
        }
        if (!$coupon->is_active) {
            throw new Exception('This coupon is not active.');
        }
        if (!$coupon->isWithinValidityPeriod()) {
            throw new Exception('This coupon is expired or not yet valid.');
        }
        if ($coupon->hasReachedLimit()) {
            throw new Exception('This coupon has reached its usage limit.');
        }
        if ($subtotal < (float) $coupon->min_order_amount) {
            throw new Exception(
                sprintf('The minimum order amount for this coupon is %s.', number_format((float) $coupon->min_order_amount, 2))
            );
        }
        return $coupon;
    }
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->isPercent()) {
            $discount = $subtotal * ((float) $coupon->value / 100);
            if ($coupon->max_discount !== null) {
                $discount = min($discount, (float) $coupon->max_discount);
            }
        } else {
            $discount = (float) $coupon->value;
        }
        $discount = min($discount, $subtotal);
        return round($discount, 2);
    }
}