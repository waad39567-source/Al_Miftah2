<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Property $property): bool
    {
        if ($user && $user->isAdmin()) {
            return true;
        }
        if ($property->status === 'active') {
            return true;
        }
        if ($user && $property->owner_id === $user->id) {
            return true;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Property $property): bool
    {
        return $user->isAdmin() || $property->owner_id === $user->id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->isAdmin() || $property->owner_id === $user->id;
    }

    public function addImages(User $user, Property $property): bool
    {
        return $user->isAdmin() || $property->owner_id === $user->id;
    }

    public function deleteImage(User $user, Property $property): bool
    {
        return $user->isAdmin() || $property->owner_id === $user->id;
    }

    public function markAsRented(User $user, Property $property): bool
    {
        return $user->isAdmin() || $property->owner_id === $user->id;
    }

    public function markAsSold(User $user, Property $property): bool
    {
        return $user->isAdmin() || $property->owner_id === $user->id;
    }

    public function viewAnyForAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public function approve(User $user, Property $property): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, Property $property): bool
    {
        return $user->isAdmin();
    }
}
