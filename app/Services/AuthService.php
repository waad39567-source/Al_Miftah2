<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'user',
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        return [
            'user' => $user,
        ];
    }

    public function login(array $data): array|false|null|string
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            return false;
        }

        if ($user->is_banned) {
            return 'banned';
        }

        if (is_null($user->email_verified_at)) {
            return 'unverified';
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function changePassword(User $user, array $data): bool
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $user->tokens()->delete();

        return true;
    }

    public function promoteToAdmin(array $data): ?User
    {
        $user = null;

        if (isset($data['id'])) {
            $user = User::find($data['id']);
        } elseif (isset($data['email'])) {
            $user = User::where('email', $data['email'])->first();
        }

        if (!$user) {
            return null;
        }

        $user->update(['role' => 'admin']);

        return $user;
    }

    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
