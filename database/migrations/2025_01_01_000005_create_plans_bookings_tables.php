<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gym_plans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('duration_type', [
                'daily', 'weekly', 'monthly', 'quarterly',
                'half_yearly', 'yearly', 'personal_training', 'diet_consultation',
            ]);
            $t->unsignedInteger('duration_days');
            $t->decimal('price', 10, 2);
            $t->decimal('discount_price', 10, 2)->nullable();
            $t->text('description')->nullable();
            $t->json('features')->nullable();
            $t->boolean('is_active')->default(true)->index();
            $t->boolean('is_popular')->default(false);
            $t->timestamps();
            $t->index(['gym_id', 'duration_type']);
        });

        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->enum('type', ['flat', 'percentage']);
            $t->decimal('value', 10, 2);
            $t->decimal('max_discount', 10, 2)->nullable();
            $t->decimal('min_order_value', 10, 2)->default(0);
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('per_user_limit')->default(1);
            $t->unsignedInteger('used_count')->default(0);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->foreignId('gym_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->string('booking_number')->unique();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('gym_id')->constrained()->restrictOnDelete();
            $t->foreignId('gym_plan_id')->constrained()->restrictOnDelete();
            $t->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('base_amount', 10, 2);
            $t->decimal('discount_amount', 10, 2)->default(0);
            $t->decimal('tax_amount', 10, 2)->default(0);
            $t->decimal('total_amount', 10, 2);
            $t->enum('status', [
                'pending_payment', 'confirmed', 'active', 'expired', 'cancelled', 'refunded',
            ])->default('pending_payment')->index();
            $t->date('starts_on');
            $t->date('ends_on');
            $t->text('notes')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->text('cancellation_reason')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'status']);
            $t->index(['gym_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('gateway')->default('razorpay');
            $t->string('gateway_order_id')->nullable();
            $t->string('gateway_payment_id')->nullable();
            $t->string('gateway_signature')->nullable();
            $t->decimal('amount', 10, 2);
            $t->string('currency', 3)->default('INR');
            $t->enum('status', ['created', 'authorized', 'captured', 'failed', 'refunded'])
              ->default('created')->index();
            $t->string('method')->nullable();      // card | upi | netbanking
            $t->json('raw_response')->nullable();
            $t->timestamp('captured_at')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->decimal('refund_amount', 10, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('coupon_usage', function (Blueprint $t) {
            $t->id();
            $t->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $t->decimal('discount_amount', 10, 2);
            $t->timestamps();
            $t->index(['coupon_id', 'user_id']);
        });

        Schema::create('membership_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->foreignId('gym_plan_id')->constrained()->cascadeOnDelete();
            $t->date('starts_on');
            $t->date('ends_on');
            $t->enum('status', ['active', 'expired', 'cancelled'])->default('active')->index();
            $t->timestamps();
        });

        Schema::create('offers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->decimal('discount_percentage', 5, 2)->default(0);
            $t->string('banner_url')->nullable();
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
        Schema::dropIfExists('membership_history');
        Schema::dropIfExists('coupon_usage');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('gym_plans');
    }
};
