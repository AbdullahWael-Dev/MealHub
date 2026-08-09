<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ValidateCouponRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ApiResponseTrait;
    public function __construct(private readonly \App\Services\V1\CouponServices\CouponService $couponService) {}

    public function validate(ValidateCouponRequest $request)
    {
        $code = $request->validated('code');
        $subtotal = (float) $request->validated('subtotal');
        try {
            $coupon = $this->couponService->validateCoupon($code, (float) $subtotal);
            $discount = $this->couponService->calculateDiscount($coupon, (float) $subtotal);
            $finalTotal = round($subtotal - $discount, 2);
            return $this->successResponse([
                'valid' => true,
                'code' => $coupon->code,
                'discount' => $discount,
                'subtotal' => round($subtotal, 2),
                'final_total' => $finalTotal,
            ], 'Coupon is valid.');
        } catch (\Exception $e) {
            return $this->errorResponse([
                'valid' => false,
                'code' => $code,
            ], $e->getMessage(), 422);
        }
    }
}
