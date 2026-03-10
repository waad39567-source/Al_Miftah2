<?php

namespace App\Policies;

use App\Models\ContactRequest;
use App\Models\User;

class ContactRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->email_verified_at !== null;
    }

    public function view(User $user, ContactRequest $contactRequest): bool
    {
        return $user->is_admin 
            || $contactRequest->user_id === $user->id 
            || $contactRequest->owner_id === $user->id;
    }

    public function viewMyRequests(User $user): bool
    {
        return true;
    }

    public function viewMyReceived(User $user): bool
    {
        return true;
    }

    public function viewAnyForAdmin(User $user): bool
    {
        return $user->is_admin;
    }

    public function approve(User $user, ContactRequest $contactRequest): bool
    {
        return $user->is_admin;
    }

    public function reject(User $user, ContactRequest $contactRequest): bool
    {
        return $user->is_admin;
    }
}
