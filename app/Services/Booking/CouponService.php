<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function calculateDiscount(Coupon $coupon, float $baseAmount, User $user, int $gymId): float
    {
        if (! $coupon->isCurrentlyValid()) {
            throw ValidationException::withMessages(['coupon' => ['Coupon is expired or inactive.']]);
        }

        if ($coupon->gym_id && $coupon->gym_id !== $gymId) {
            throw ValidationException::withMessages(['coupon' => ['Coupon is not valid for this gym.']]);
        }

        if ($baseAmount < (float) $coupon->min_order_value) {
            throw ValidationException::withMessages([
                'coupon' => ["Minimum order value is ₹{$coupon->min_order_value}."],
            ]);
        }

        $used = $user->bookings()
            ->whereNotNull('coupon_id')
            ->where('coupon_id', $coupon->id)
            ->count();

        if ($used >= $coupon->per_user_limit) {
            throw ValidationException::withMessages(['coupon' => ['You have already used this coupon.']]);
        }

        $discount = $coupon->type === 'flat'
            ? (float) $coupon->value
            : ($baseAmount * (float) $coupon->value / 100);

        if ($coupon->max_discount) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return round(min($discount, $baseAmount), 2);
    }

    public function registerUsage(Booking $booking): void
    {
        $coupon = Coupon::find($booking->coupon_id);
        if (! $coupon) return;

        $coupon->increment('used_count');

        \DB::table('coupon_usage')->insert([
            'coupon_id' => $coupon->id,
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'discount_amount' => $booking->discount_amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
