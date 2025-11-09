<?php

namespace App\Service;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and company
     */
    public function registerUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create company
            $company = Company::create([
                'name' => $data['company_name'],
                'email' => $data['company_email'] ?? null,
                'phone' => $data['company_phone'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? 'CM',
                'currency' => 'XAF',
                'status' => 'active',
                'subscription_plan' => 'free',
            ]);

            // Create user
            $user = User::create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
            ]);

            // Assign admin role to first user
            $user->assignRole('admin');

            // Update company owner
            $company->update(['user_id' => $user->id]);

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load('company', 'roles'),
                'company' => $company,
                'token' => $token,
            ];
        });
    }

    /**
     * Authenticate user
     */
    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Votre compte est désactivé. Veuillez contacter l\'administrateur.'],
            ]);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('company', 'roles'),
            'token' => $token,
        ];
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(User $user)
    {
        $user->currentAccessToken()->delete();
        return true;
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data)
    {
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'phone' => $data['phone'] ?? $user->phone,
            'avatar' => $data['avatar'] ?? $user->avatar,
        ]);

        return $user->fresh()->load('company', 'roles');
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, array $data)
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->fresh()->load('company', 'roles'),
            'token' => $token,
        ];
    }
}
