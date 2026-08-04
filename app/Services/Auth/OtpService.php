<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * DUMMY OTP Service.
 * In production, integrate an SMS provider (Twilio, MSG91, etc.).
 * Here we generate a code, log it, and store its hash in cache.
 */
class OtpService
{
    protected int $ttl;

    public function __construct()
    {
        $this->ttl = (int) config('services.otp.ttl_seconds', 300);
    }

    public function send(string $phone): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($this->key($phone), Hash::make($code), $this->ttl);

        // DUMMY: in real integration, call SMS gateway here.
        logger()->info("OTP for $phone => $code");

        return app()->isProduction() ? '' : $code; // return in dev only
    }

    public function verify(string $phone, string $code): User
    {
        $hash = Cache::get($this->key($phone));
        if (! $hash || ! Hash::check($code, $hash)) {
            throw ValidationException::withMessages(['code' => ['Invalid or expired OTP.']]);
        }
        Cache::forget($this->key($phone));

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'User '.substr($phone, -4),
                'email' => $phone.'@phone.gymfinder.local',
                'password' => Hash::make(str()->random(24)),
                'provider' => 'phone',
                'phone_verified_at' => now(),
            ]
        );

        return $user;
    }

    protected function key(string $phone): string
    {
        return "otp:phone:$phone";
    }
}
