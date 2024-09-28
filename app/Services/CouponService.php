<?php

namespace App\Services;

use App\Enums\CouponTypeEnum;
use App\Models\Coupon;
use Carbon\Carbon;

/**
 * Class CouponService.
 */
class CouponService
{
    public function getCouponByCode($couponCode)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if ($this->isValidCoupon($coupon)) {
            return $coupon;
        }
        return null;
    }

    public function isValidCoupon(?Coupon $coupon): bool
    {
        // Return false if there is no coupon
        if (!$coupon) {
            return false;
        }
        // Check if the coupon has expired
        if ($coupon->expires_at && Carbon::now()->gt($coupon->expires_at)) {
            return false;
        }
        // Check total max uses of this coupon
        if (!is_null($coupon->max_uses) && $coupon->used_times >= $coupon->max_uses) {
            return false;
        }
        // Check max uses per user, if a user is authenticated
        $user = auth('sanctum')->user();
        if ($user) {
            $usage = $coupon->users()->where('user_id', $user->id)->first();
            if ($usage && $usage->pivot->uses >= $coupon->max_uses_per_user) {
                return false;
            }
        }
        // If all checks pass, the coupon is valid
        return true;
    }

    public function calculateDiscount($coupon, $amount)
    {
        $discount = 0.0;
        if ($coupon->discount_type == CouponTypeEnum::FIXED->value) {
            $discount = $this->calculateFixedDiscount($coupon, $amount);
        } elseif ($coupon->discount_type == CouponTypeEnum::PERCENT->value) {
            $discount = $this->calculatePercentageDiscount($coupon, $amount);
        }
        return $discount;
    }

    protected function calculatePercentageDiscount($coupon, $amount)
    {
        return $amount * ($coupon->discount_value / 100);
    }

    protected function calculateFixedDiscount($coupon, $amount)
    {
        return min($coupon->discount_value, $amount);
    }

    public function incrementUsedTimes(string $couponCode): bool
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if ($coupon) {
            $coupon->update(['used_times' => $coupon->used_times + 1]);
            return true;
        }

        return false;
    }

    public function incrementUserCouponUsage($coupon, $user)
    {
        $usage = $coupon->users()->where('user_id', $user->id)->first();

        if ($usage) {
            $currentUses = $usage->pivot->uses;
            $coupon->users()->updateExistingPivot($user->id, ['uses' => $currentUses + 1]);
        } else {
            $coupon->users()->attach($user->id, ['uses' => 1]);
        }
    }
}
