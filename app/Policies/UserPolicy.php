<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->isAdmin() || $user->id === $targetUser->id;
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->isAdmin() || $user->id === $targetUser->id;
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && $user->id !== $targetUser->id;
    }

    public function promoteToAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public function verifyUser(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && $user->id !== $targetUser->id;
    }

    public function banUser(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && $user->id !== $targetUser->id;
    }

    public function unbanUser(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && $user->id !== $targetUser->id;
    }

    public function toggleUserActive(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && $user->id !== $targetUser->id;
    }
}
