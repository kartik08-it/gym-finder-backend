<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\Auth\OtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected OtpService $otp,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->register($request->validated());
        return $this->ok([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registered successfully.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->email, $request->password);
        return $this->ok([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());
        return $this->ok(null, 'Logged out.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok(new UserResource($request->user()));
    }

    // -------- Password reset --------
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));
        return $this->ok(['status' => $status], __($status));
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        return $this->ok(['status' => $status], __($status));
    }

    // -------- Email verification --------
    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);
        if (! hash_equals(sha1($user->email), $hash)) {
            return $this->fail('Invalid verification link.', 403);
        }
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        return $this->ok(null, 'Email verified.');
    }

    // -------- DUMMY Google login --------
    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string'],
            'sub' => ['nullable', 'string'],
            'picture' => ['nullable', 'string'],
        ]);
        $result = $this->auth->loginWithGoogleDummy($request->only(['email', 'name', 'sub', 'picture']));
        return $this->ok([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Google login successful (dummy).');
    }

    // -------- Phone OTP --------
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string', 'min:8', 'max:20']]);
        $code = $this->otp->send($request->phone);
        return $this->ok([
            'phone' => $request->phone,
            'dev_code' => $code ?: null,   // shown in dev only
        ], 'OTP sent (dummy).');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);
        $user = $this->otp->verify($request->phone, $request->code);
        return $this->ok([
            'user' => new UserResource($user),
            'token' => $user->createToken('otp-auth')->plainTextToken,
        ], 'OTP verified.');
    }
}
