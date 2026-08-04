<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\GymPlan;
use App\Services\Booking\BookingService;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookings,
        protected RazorpayService $razorpay,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['gym', 'plan', 'payment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return $this->ok([
            'items' => BookingResource::collection($bookings->items()),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'gym_plan_id' => 'required|exists:gym_plans,id',
            'coupon_code' => 'nullable|string|max:40',
        ]);
        $plan = GymPlan::findOrFail($data['gym_plan_id']);
        $quote = $this->bookings->quote($plan, $data['coupon_code'] ?? null, $request->user());
        return $this->ok($quote);
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        $plan = GymPlan::findOrFail($request->gym_plan_id);
        $booking = $this->bookings->create($request->user(), $plan, $request->coupon_code);
        $payment = $this->razorpay->createOrder($booking);

        return $this->ok([
            'booking' => new BookingResource($booking->load(['gym', 'plan'])),
            'payment' => [
                'gateway' => $payment->gateway,
                'gateway_order_id' => $payment->gateway_order_id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'key' => config('services.razorpay.key'),
            ],
        ], 'Booking created. Proceed to payment.', 201);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);
        return $this->ok(new BookingResource($booking->load(['gym', 'plan', 'payment'])));
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);
        $data = $request->validate(['reason' => 'nullable|string|max:500']);
        $this->bookings->cancel($booking, $data['reason'] ?? null);
        return $this->ok(new BookingResource($booking->fresh()), 'Booking cancelled.');
    }

    public function invoice(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);
        return $this->ok([
            'booking' => new BookingResource($booking->load(['gym', 'plan', 'payment'])),
            'invoice_url' => url("/api/v1/bookings/{$booking->id}/invoice.pdf"),
        ]);
    }
}
