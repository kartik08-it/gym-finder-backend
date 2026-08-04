<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\GymPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(protected CouponService $couponService) {}

    /**
     * Compute booking totals given a plan + optional coupon.
     */
    public function quote(GymPlan $plan, ?string $couponCode, User $user): array
    {
        $base = $plan->effectivePrice();
        $discount = 0.0;
        $coupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if (! $coupon) {
                throw ValidationException::withMessages(['coupon' => ['Coupon not found.']]);
            }
            $discount = $this->couponService->calculateDiscount($coupon, $base, $user, $plan->gym_id);
        }

        $taxable = max(0, $base - $discount);
        $tax = round($taxable * 0.18, 2);   // 18% GST placeholder
        $total = round($taxable + $tax, 2);

        return [
            'base_amount' => round($base, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => $tax,
            'total_amount' => $total,
            'coupon_id' => $coupon?->id,
        ];
    }

    /**
     * Create a pending-payment booking.
     */
    public function create(User $user, GymPlan $plan, ?string $couponCode = null): Booking
    {
        return DB::transaction(function () use ($user, $plan, $couponCode) {
            $quote = $this->quote($plan, $couponCode, $user);

            $start = now()->toDateString();
            $end = now()->addDays($plan->duration_days)->toDateString();

            return Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'user_id' => $user->id,
                'gym_id' => $plan->gym_id,
                'gym_plan_id' => $plan->id,
                'coupon_id' => $quote['coupon_id'],
                'base_amount' => $quote['base_amount'],
                'discount_amount' => $quote['discount_amount'],
                'tax_amount' => $quote['tax_amount'],
                'total_amount' => $quote['total_amount'],
                'status' => BookingStatus::PENDING_PAYMENT->value,
                'starts_on' => $start,
                'ends_on' => $end,
            ]);
        });
    }

    /**
     * Mark a booking as confirmed after successful payment.
     */
    public function confirm(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => BookingStatus::CONFIRMED->value,
            ]);

            if ($booking->coupon_id) {
                $this->couponService->registerUsage($booking);
            }

            event(new BookingConfirmed($booking));

            return $booking->refresh();
        });
    }

    public function cancel(Booking $booking, ?string $reason = null): Booking
    {
        $booking->update([
            'status' => BookingStatus::CANCELLED->value,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
        return $booking->refresh();
    }

    protected function generateBookingNumber(): string
    {
        return 'GYM'.now()->format('YmdHis').strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }
}
