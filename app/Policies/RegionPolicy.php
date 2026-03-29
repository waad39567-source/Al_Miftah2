<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;

class RegionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Region $region): bool
    {
        return true;
    }

    public function viewAnyForAdmin(User $user): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return true; // أي مستخدم مسجل يمكنه إضافة حي
    }

    public function update(User $user, Region $region): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Region $region): bool
    {
        return $user->is_admin;
    }
}
