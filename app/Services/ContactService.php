<?php

namespace App\Services;

use App\Models\ContactRequest as ContactRequestModel;
use App\Models\Property;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactService
{
    public function create(array $data, int $userId): ContactRequestModel
    {
        $property = Property::findOrFail($data['property_id']);

        $existingRequest = ContactRequestModel::where('property_id', $data['property_id'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            throw new \Exception('لديك طلب تواصل سابق لهذا العقار');
        }

        return ContactRequestModel::create([
            'property_id' => $data['property_id'],
            'user_id' => $userId,
            'owner_id' => $property->owner_id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = ContactRequestModel::with(['property', 'user', 'owner']);

        if (!empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }

        if (!empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function getById(int $id): ?ContactRequestModel
    {
        return ContactRequestModel::with(['property', 'user', 'owner'])->find($id);
    }

    public function getUserRequestForProperty(int $userId, int $propertyId): ?ContactRequestModel
    {
        return ContactRequestModel::with(['property', 'owner'])
            ->where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();
    }

    public function approve(ContactRequestModel $contactRequest, int $adminId): ContactRequestModel
    {
        $contactRequest->update([
            'status' => 'approved',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        return $contactRequest->fresh(['property', 'user', 'owner']);
    }

    public function reject(ContactRequestModel $contactRequest, int $adminId, string $reason): ContactRequestModel
    {
        $contactRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        return $contactRequest->fresh(['property', 'user', 'owner']);
    }
}
