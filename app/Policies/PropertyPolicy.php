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
        return true;
    }

    public function create(User $user): bool
    {
        return $user->email_verified_at !== null;
    }

    public function update(User $user, Property $property): bool
    {
        return $user->is_admin || $property->owner_id === $user->id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->is_admin || $property->owner_id === $user->id;
    }

    public function addImages(User $user, Property $property): bool
    {
        return $user->is_admin || $property->owner_id === $user->id;
    }

    public function deleteImage(User $user, Property $property): bool
    {
        return $user->is_admin || $property->owner_id === $user->id;
    }

    public function markAsRented(User $user, Property $property): bool
    {
        return $user->is_admin || $property->owner_id === $user->id;
    }

    public function markAsSold(User $user, Property $property): bool
    {
        return $user->is_admin || $property->owner_id === $user->id;
    }

    public function viewAnyForAdmin(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function approve(User $user, Property $property): bool
    {
        return (bool) $user->is_admin;
    }

    public function reject(User $user, Property $property): bool
    {
        return (bool) $user->is_admin;
    }
}
