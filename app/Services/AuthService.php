<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password']),
            'phone'     => $data['phone'],
            'role'      => 'user',
            'is_active' => true,
        ]);

        return ['user' => $user];
    }

    public function login(array $data): array|false|null|string
    {
        $loginField = $data['email'];

        $user = User::where('email', $loginField)
            ->orWhere('phone', $loginField)
            ->first();

        if (!$user || !$user->password || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        if (!$user->is_active) return false;
        if ($user->is_banned)  return 'banned';

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function loginOrRegisterWithFirebase(array $firebaseUser, FirebaseAuthService $firebaseAuthService): array|false|string
    {
        $uid      = $firebaseUser['localId'];
        $email    = $firebaseUser['email'] ?? null;
        $phone    = isset($firebaseUser['phoneNumber'])
            ? $firebaseAuthService->normalizePhone($firebaseUser['phoneNumber'])
            : null;
        $provider = $firebaseAuthService->detectProvider($firebaseUser);

        // 1. Find by firebase_uid
        $user = User::where('firebase_uid', $uid)->first();

        // 2. Find by email
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }

        // 3. Find by phone
        if (!$user && $phone) {
            $user = User::where('phone', $phone)->first();
        }

        if ($user) {
            $updates = [];
            if (!$user->firebase_uid)                              $updates['firebase_uid']  = $uid;
            if (!$user->auth_provider || $user->auth_provider === 'email') $updates['auth_provider'] = $provider;
            if (!empty($updates)) $user->update($updates);
        } else {
            $user = $this->createFromFirebase($uid, $email, $phone, $provider, $firebaseUser);
        }

        if (!$user->is_active) return false;
        if ($user->is_banned)  return 'banned';

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    private function createFromFirebase(string $uid, ?string $email, ?string $phone, string $provider, array $firebaseUser): User
    {
        $name = $firebaseUser['displayName'] ?? null;

        if (empty($name) && $email)  $name = explode('@', $email)[0];
        if (empty($name) && $phone)  $name = 'User_' . substr($phone, -4);
        if (empty($name))            $name = 'User';

        return User::create([
            'name'              => $name,
            'email'             => $email,
            'phone'             => $phone,
            'password'          => Hash::make(Str::random(32)),
            'firebase_uid'      => $uid,
            'auth_provider'     => $provider,
            'role'              => 'user',
            'is_active'         => true,
            'email_verified_at' => !empty($firebaseUser['emailVerified']) ? now() : null,
        ]);
    }

    public function setFirebasePassword(User $user, string $newPassword): bool
    {
        $user->update(['password' => Hash::make($newPassword)]);
        return true;
    }

    public function changePassword(User $user, array $data): bool
    {
        if (!$user->password || !Hash::check($data['current_password'], $user->password)) {
            return false;
        }

        $user->update(['password' => Hash::make($data['password'])]);
        $user->tokens()->delete();

        return true;
    }

    public function promoteToAdmin(array $data): ?User
    {
        $user = null;

        if (isset($data['id']))    $user = User::find($data['id']);
        elseif (isset($data['email'])) $user = User::where('email', $data['email'])->first();

        if (!$user) return null;

        $user->update(['role' => 'admin']);

        return $user;
    }

    public function createUser(array $data): User
    {
        return User::create([
            'name'      => $data['name'],
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password']),
            'phone'     => $data['phone'],
            'role'      => $data['role'] ?? 'user',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
