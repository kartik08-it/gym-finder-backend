<?php

namespace App\Services\Payment;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Booking\BookingService;
use Illuminate\Support\Facades\Log;

/**
 * DUMMY Razorpay gateway.
 *
 * In production replace with the razorpay/razorpay SDK:
 *   $api = new \Razorpay\Api\Api($key, $secret);
 *   $order = $api->order->create([...]);
 *   $api->utility->verifyPaymentSignature([...]);
 *
 * Here we simulate order + signature to keep the flow end-to-end.
 */
class RazorpayService
{
    public function __construct(protected BookingService $bookings) {}

    public function createOrder(Booking $booking): Payment
    {
        $orderId = 'order_'.strtoupper(bin2hex(random_bytes(8)));

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'gateway' => 'razorpay',
            'gateway_order_id' => $orderId,
            'amount' => $booking->total_amount,
            'currency' => 'INR',
            'status' => 'created',
        ]);

        Log::info("[DUMMY Razorpay] Created order $orderId for booking {$booking->booking_number}");

        return $payment;
    }

    /**
     * Verify signature — DUMMY implementation accepts anything with the right prefix.
     * Real: hash_hmac('sha256', "$orderId|$paymentId", $secret) === $signature
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (app()->environment('production')) {
            $expected = hash_hmac('sha256', "$orderId|$paymentId", (string) config('services.razorpay.secret'));
            return hash_equals($expected, $signature);
        }
        // DUMMY: dev/staging fallback
        return str_starts_with($paymentId, 'pay_') && str_starts_with($orderId, 'order_');
    }

    public function capture(Payment $payment, string $paymentId, string $signature, ?string $method = 'card'): Payment
    {
        $valid = $this->verifySignature($payment->gateway_order_id, $paymentId, $signature);

        $payment->update([
            'gateway_payment_id' => $paymentId,
            'gateway_signature' => $signature,
            'status' => $valid ? 'captured' : 'failed',
            'method' => $method,
            'captured_at' => $valid ? now() : null,
            'raw_response' => ['dummy' => true, 'verified' => $valid],
        ]);

        if ($valid) {
            $this->bookings->confirm($payment->booking);
        }

        return $payment->refresh();
    }

    public function refund(Payment $payment, ?float $amount = null): Payment
    {
        $amt = $amount ?? (float) $payment->amount;
        $payment->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_amount' => $amt,
        ]);
        return $payment;
    }
}
