<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    use ApiResponse;

    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            Log::channel('auth')->info('User registered successfully', [
                'email' => $result['user']->email,
                'user_id' => $result['user']->id,
                'ip' => $request->ip(),
                'company' => $result['company'],
                'user_agent' => $request->userAgent()
            ]);

            return $this->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'company' => $result['company']
            ], 'Accoun Create successfully', 201);
        } catch (\Exception $e) {
            Log::channel('auth')->error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error('Registration failed', 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            Log::channel('auth')->info('User logged in successfully', [
                'email' => $request->email,
                'user_id' => $result['user']->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token']
            ], 'Login successful');
        } catch (\Exception $e) {
            Log::channel('auth')->warning('Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return $this->error('Invalid credentials', 401);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            Log::channel('auth')->info('User logged out', [
                'user_id' => $request->user()->id,
                'ip' => $request->ip()
            ]);

            return $this->success([], 'Logout successful');
        } catch (\Exception $e) {
            Log::channel('auth')->error('Logout failed', [
                'user_id' => $request->user()->id ?? 'unknown',
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return $this->error('Logout failed', 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            return $this->success(
                new UserResource($request->user()),
                'User data retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::channel('auth')->error('Failed to retrieve user data', [
                'user_id' => $request->user()->id ?? 'unknown',
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return $this->error('Failed to retrieve user data', 500);
        }
    }
}
