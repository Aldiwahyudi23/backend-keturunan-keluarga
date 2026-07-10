<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $data = $this
            ->authService
            ->login(
                $credentials,
                $request->ip()
            );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => $data,
        ]);
    }

    /**
     * Logout current device
     */
    public function logout(Request $request)
    {
        $this
            ->authService
            ->logout(
                $request->user()
            );

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Logout all devices
     */
    public function logoutAll(Request $request)
    {
        $this
            ->authService
            ->logoutAll(
                $request->user()
            );

        return response()->json([
            'success' => true,
            'message' => 'Logout semua device berhasil.',
        ]);
    }

    /**
     * Current User
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this
                ->authService
                ->me(
                    $request->user()
                ),
        ]);
    }
}
