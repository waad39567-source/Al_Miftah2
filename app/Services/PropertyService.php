<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Pagination\LengthAwarePaginator;

class PropertyService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Property::with(['owner', 'region', 'images']);

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

        $query->where('is_active', true);

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function getById(int $id): ?Property
    {
        return Property::with(['owner', 'region', 'images'])->find($id);
    }

    public function create(array $data, int $userId): Property
    {
        return Property::create([
            'owner_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'type' => $data['type'],
            'property_type' => $data['property_type'],
            'area' => $data['area'],
            'region_id' => $data['region_id'],
            'location' => $data['location'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => 'pending',
            'is_active' => true,
        ]);
    }

    public function update(Property $property, array $data): Property
    {
        $property->update($data);
        return $property->fresh(['owner', 'region', 'images']);
    }

    public function delete(Property $property): bool
    {
        return $property->delete();
    }

    public function approve(Property $property, int $adminId): Property
    {
        $property->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
        return $property;
    }

    public function reject(Property $property, int $adminId, string $reason): Property
    {
        $property->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
        return $property;
    }

    public function getAllForAdmin(array $filters): LengthAwarePaginator
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
}
