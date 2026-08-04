<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'user_id', 'gateway', 'gateway_order_id', 'gateway_payment_id',
        'gateway_signature', 'amount', 'currency', 'status', 'method',
        'raw_response', 'captured_at', 'refunded_at', 'refund_amount',
    ];

    protected function casts(): array
    {
        return [
            'raw_response' => 'array',
            'amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'captured_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
