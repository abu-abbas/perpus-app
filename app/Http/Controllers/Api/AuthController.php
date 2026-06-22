<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk autentikasi — login, logout, dan data user.
 */
class AuthController extends Controller
{
    /**
     * Proses login via Sanctum cookie-based SPA authentication.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => true,
                'type' => 'business',
                'code' => 'LOGIN_FAILED',
                'message' => 'Email atau password salah.',
            ], 422);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => new UserResource(Auth::user()),
        ]);
    }

    /**
     * Proses logout — hapus sesi.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        try {
            Auth::shouldUse('web');
        } catch (\Throwable $e) {
        }

        try {
            $sanctumGuard = Auth::guard('sanctum');
            $ref = new \ReflectionClass($sanctumGuard);
            if ($ref->hasProperty('user')) {
                $prop = $ref->getProperty('user');
                $prop->setAccessible(true);
                $prop->setValue($sanctumGuard, null);
            }
        } catch (\Throwable $e) {
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Ambil data user yang sedang login.
     */
    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
