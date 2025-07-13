<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;


class AuthService
{
    /**
     * Register new user
     */

    public function __construct(
        private CompanyService $companyService
    ) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);


            $company = $this->companyService->create([
                'user_id' => $user->id,
                'name' => $data['company_name'],
            ]);
            $token_secret_key = env('TOKEN_SECRET_KEY');
            $token = $user->createToken($token_secret_key, [
                'role' => 'COMPANY',
                'company_id' => $company->id
            ])->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
                'company' => $company,
                'role' => 'COMPANY'
            ];
        });
    }

    /**
     * Login user
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        // Optionnel : révoquer les anciens tokens
        $user->tokens()->delete();

        $token_secret_key = env('TOKEN_SECRET_KEY');

        $token = $user->createToken($token_secret_key)->plainTextToken;
        $enterprises = $user->companies()->get();

        return [
            'user' => $user,
            'token' => $token,
            'enterprises' => $enterprises,
            'default_enterprise' => $this->companyService->getDefaultEnterpriseOfUser($user->id)
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
