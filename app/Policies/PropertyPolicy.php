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
        return true;
    }

    public function update(User $user, Property $property): bool
    {
        return true; // أي مستخدم يمكنه التحديث
    }

    public function delete(User $user, Property $property): bool
    {
        return true; // أي مستخدم يمكنه الحذف
    }

    public function addImages(User $user, Property $property): bool
    {
        return true; // أي مستخدم يمكنه الإضافة
    }

    public function deleteImage(User $user, Property $property): bool
    {
        return true; // أي مستخدم يمكنه الحذف
    }

    public function markAsRented(User $user, Property $property): bool
    {
        return true; // أي مستخدم يمكنه التحديد
    }

    public function markAsSold(User $user, Property $property): bool
    {
        return true; // أي مستخدم يمكنه التحديد
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
