<?php

namespace App\Services;

use App\Models\User;
use App\Models\Property;
use App\Models\ContactRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminService
{
    public function getUsers(array $filters): LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_verified'])) {
            if ($filters['is_verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if (isset($filters['is_banned'])) {
            $query->where('is_banned', $filters['is_banned']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function getUnverifiedUsers(array $filters): LengthAwarePaginator
    {
        $query = User::whereNull('email_verified_at');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function verifyUser(User $user): User
    {
        $user->update([
            'email_verified_at' => now(),
        ]);
        return $user->fresh();
    }

    public function banUser(User $user, string $reason = null): User
    {
        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'is_active' => false,
        ]);
        
        if ($reason) {
            $user->ban_reason = $reason;
            $user->save();
        }
        
        return $user->fresh();
    }

    public function unbanUser(User $user): User
    {
        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'is_active' => true,
        ]);
        
        return $user->fresh();
    }

    public function toggleUserActive(User $user): User
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);
        
        return $user->fresh();
    }

    public function getProperties(array $filters): LengthAwarePaginator
    {
        $query = Property::with(['owner', 'region']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function approveProperty(Property $property, int $adminId): Property
    {
        $property->update([
            'status' => 'active',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
        return $property;
    }

    public function rejectProperty(Property $property, int $adminId, string $reason): Property
    {
        $property->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
        return $property;
    }

    public function getContactRequests(array $filters): LengthAwarePaginator
    {
        $query = ContactRequest::with(['property', 'user', 'owner']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function approveContactRequest(ContactRequest $contactRequest, int $adminId): ContactRequest
    {
        $contactRequest->update([
            'status' => 'approved',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        return $contactRequest->fresh(['property', 'user', 'owner']);
    }

    public function rejectContactRequest(ContactRequest $contactRequest, int $adminId, string $reason): ContactRequest
    {
        $contactRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        return $contactRequest->fresh(['property', 'user', 'owner']);
    }

    public function getStatistics(): array
    {
        $today = now()->startOfDay();
        
        $usersCount = User::count();
        $ownersCount = User::whereHas('properties')->count();
        $unverifiedUsers = User::whereNull('email_verified_at')->count();
        $bannedUsers = User::where('is_banned', true)->count();
        $propertiesCount = Property::count();
        $propertiesToday = Property::where('created_at', '>=', $today)->count();
        
        $activeProperties = Property::where('status', 'active')->count();
        $rentedProperties = Property::where('status', 'rented')->count();
        $soldProperties = Property::where('status', 'sold')->count();
        
        $contactRequestsCount = ContactRequest::count();
        $pendingContactRequests = ContactRequest::where('status', 'pending')->count();
        
        $pendingProperties = Property::where('status', 'pending')->count();
        $rejectedProperties = Property::where('status', 'rejected')->count();

        return [
            'users' => [
                'total' => $usersCount,
                'owners' => $ownersCount,
                'unverified' => $unverifiedUsers,
                'banned' => $bannedUsers,
            ],
            'properties' => [
                'total' => $propertiesCount,
                'today' => $propertiesToday,
                'active' => $activeProperties,
                'rented' => $rentedProperties,
                'sold' => $soldProperties,
                'pending' => $pendingProperties,
                'rejected' => $rejectedProperties,
            ],
            'contact_requests' => [
                'total' => $contactRequestsCount,
                'pending' => $pendingContactRequests,
            ],
        ];
    }
}
