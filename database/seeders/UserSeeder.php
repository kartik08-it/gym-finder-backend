<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'GymFinder Admin',
            'email' => 'admin@gymfinder.app',
            'password' => Hash::make('Admin@12345'),
            'role' => UserRole::ADMIN->value,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Rahul Owner',
            'email' => 'owner@gymfinder.app',
            'phone' => '+919000000001',
            'password' => Hash::make('Owner@12345'),
            'role' => UserRole::GYM_OWNER->value,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Priya Customer',
            'email' => 'customer@gymfinder.app',
            'phone' => '+919000000002',
            'password' => Hash::make('Customer@12345'),
            'role' => UserRole::CUSTOMER->value,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
