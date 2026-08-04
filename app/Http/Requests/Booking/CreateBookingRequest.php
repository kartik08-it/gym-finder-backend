<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isCustomer() ?? false; }

    public function rules(): array
    {
        return [
            'gym_plan_id' => ['required', 'exists:gym_plans,id'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
