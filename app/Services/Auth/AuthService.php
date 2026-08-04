<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? UserRole::CUSTOMER->value,
            'provider' => 'email',
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Login with email + password.
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended.'],
            ]);
        }

        return [
            'user' => $user,
            'token' => $user->createToken('auth')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * DUMMY Google login — accepts a fake "google_token" and returns/creates a user.
     * Replace with real Socialite / Firebase Identity Platform integration later.
     */
    public function loginWithGoogleDummy(array $data): array
    {
        $email = $data['email'];
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $data['name'] ?? 'Google User',
                'password' => Hash::make(str()->random(24)),
                'provider' => 'google',
                'provider_id' => $data['sub'] ?? str()->uuid(),
                'email_verified_at' => now(),
                'avatar_url' => $data['picture'] ?? null,
            ]
        );

        return [
            'user' => $user,
            'token' => $user->createToken('google-auth')->plainTextToken,
        ];
    }
}
