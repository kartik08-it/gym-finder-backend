<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'code' => 'WELCOME20',
            'type' => 'percentage',
            'value' => 20,
            'max_discount' => 500,
            'min_order_value' => 500,
            'usage_limit' => 1000,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);
        Coupon::create([
            'code' => 'FLAT200',
            'type' => 'flat',
            'value' => 200,
            'min_order_value' => 1000,
            'usage_limit' => 500,
            'per_user_limit' => 2,
            'is_active' => true,
        ]);
    }
}
