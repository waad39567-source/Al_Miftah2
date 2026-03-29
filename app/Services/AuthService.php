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
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'role' => 'user',
            'is_active' => true,
        ]);

        return [
            'user' => $user,
        ];
    }

    public function login(array $data): array|false|null|string
    {
        $loginField = $data['email'];

        $user = User::where('email', $loginField)
            ->orWhere('phone', $loginField)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            return false;
        }

        if ($user->is_banned) {
            return 'banned';
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
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'role' => $data['role'] ?? 'user',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
