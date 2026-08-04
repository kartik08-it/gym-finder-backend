<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected RazorpayService $razorpay) {}

    /**
     * POST /api/v1/payments/verify
     * Called by frontend after Razorpay checkout succeeds.
     */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $payment = Payment::where('gateway_order_id', $data['razorpay_order_id'])->firstOrFail();
        if ($payment->user_id !== $request->user()->id) {
            return $this->fail('Unauthorized.', 403);
        }

        $payment = $this->razorpay->capture(
            $payment,
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
            $request->input('method', 'card'),
        );

        if ($payment->status !== 'captured') {
            return $this->fail('Payment verification failed.', 422);
        }

        return $this->ok([
            'payment' => [
                'status' => $payment->status,
                'gateway_payment_id' => $payment->gateway_payment_id,
            ],
            'booking' => new BookingResource(Booking::with(['gym', 'plan'])->find($payment->booking_id)),
        ], 'Payment successful.');
    }

    /**
     * POST /api/v1/payments/webhook — Razorpay webhook (dummy).
     */
    public function webhook(Request $request): JsonResponse
    {
        // In production, verify X-Razorpay-Signature header here.
        logger()->info('Razorpay webhook received', $request->all());
        return $this->ok(null, 'Webhook received.');
    }
}
