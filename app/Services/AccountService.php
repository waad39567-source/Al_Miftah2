<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    public function deleteAccount(User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        foreach ($user->properties as $property) {
            Storage::disk('public')->deleteDirectory('properties/' . $property->id);
        }

        $user->delete();

        return true;
    }

    public function updateEmail(User $user, string $email, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return false;
        }

        $user->update(['email' => $email]);

        return true;
    }
}
