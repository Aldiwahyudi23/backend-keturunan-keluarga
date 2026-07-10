<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Login User
     */
    public function login(array $credentials, string $ip): array
    {
        $throttleKey =
            Str::lower($credentials['email'])
            .'|'
            .$ip;

        if (
            RateLimiter::tooManyAttempts(
                $throttleKey,
                5
            )
        ) {

            $seconds = RateLimiter::availableIn(
                $throttleKey
            );

            throw ValidationException::withMessages([
                'email' => [
                    "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
                ],
            ]);
        }

        if (
            ! Auth::attempt($credentials)
        ) {

            RateLimiter::hit(
                $throttleKey,
                60
            );

            throw ValidationException::withMessages([
                'email' => [
                    'Email atau password salah.',
                ],
            ]);
        }

        RateLimiter::clear(
            $throttleKey
        );

        /** @var User $user */
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Jika ingin hanya 1 device login
        |--------------------------------------------------------------------------
        */

        // $user->tokens()->delete();

        $token = $user
            ->createToken('web')
            ->plainTextToken;

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /**
     * Logout current device
     */
    public function logout(User $user): void
    {
        $user
            ->currentAccessToken()
            ?->delete();
    }

    /**
     * Logout all devices
     */
    public function logoutAll(User $user): void
    {
        $user
            ->tokens()
            ->delete();
    }

    /**
     * Current User
     */
    public function me(User $user): array
    {
        return [
            'auth_check' => auth()->check(),
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
