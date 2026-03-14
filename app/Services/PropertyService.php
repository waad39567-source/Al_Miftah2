<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\ContactRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class PropertyService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Property::with(['owner', 'region', 'images']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (!empty($filters['region_id'])) {
            $region = \App\Models\Region::find($filters['region_id']);
            if ($region) {
                $regionIds = $region->getAllDescendantIds();
                $query->whereIn('region_id', $regionIds);
            }
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['min_area'])) {
            $query->where('area', '>=', $filters['min_area']);
        }

        if (!empty($filters['max_area'])) {
            $query->where('area', '<=', $filters['max_area']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (empty($filters['show_all'])) {
            $query->where('status', 'active');
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

    public function getUserContactRequest(int $userId, int $propertyId): ?ContactRequest
    {
        return ContactRequest::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();
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
        foreach ($property->images as $image) {
            if (file_exists(public_path($image->image_path))) {
                unlink(public_path($image->image_path));
            }
            $image->delete();
        }
        return $property->delete();
    }

    public function addImages(Property $property, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->store('properties/' . $property->id, 'public');
            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => 'storage/' . $path,
                'is_primary' => $property->images()->count() === 0,
            ]);
        }
    }

    public function deleteImage(Property $property, int $imageId): bool
    {
        $image = PropertyImage::where('property_id', $property->id)
            ->where('id', $imageId)
            ->first();

        if (!$image) {
            return false;
        }

        if (file_exists(public_path($image->image_path))) {
            unlink(public_path($image->image_path));
        }

        $image->delete();
        return true;
    }

    public function approve(Property $property, int $adminId): Property
    {
        $property->update([
            'status' => 'active',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
        return $property;
    }

    public function markAsRented(Property $property): Property
    {
        $property->update(['status' => 'rented']);
        return $property;
    }

    public function markAsSold(Property $property): Property
    {
        $property->update(['status' => 'sold']);
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

        if (!empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['region_id'])) {
            $region = \App\Models\Region::find($filters['region_id']);
            if ($region) {
                $regionIds = $region->getAllDescendantIds();
                $query->whereIn('region_id', $regionIds);
            }
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

    public function search(array $filters): LengthAwarePaginator
    {
        return $this->getAll($filters);
    }

    public function advancedSearch(array $filters): LengthAwarePaginator
    {
        $query = Property::with(['owner', 'region', 'images']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (!empty($filters['region_ids'])) {
            $regionIds = explode(',', $filters['region_ids']);
            $query->whereIn('region_id', $regionIds);
        }

        if (!empty($filters['governorate_id'])) {
            $region = \App\Models\Region::find($filters['governorate_id']);
            if ($region) {
                $regionIds = $region->getAllDescendantIds();
                $query->whereIn('region_id', $regionIds);
            }
        }

        if (!empty($filters['city_id'])) {
            $region = \App\Models\Region::find($filters['city_id']);
            if ($region) {
                $regionIds = $region->getAllDescendantIds();
                $query->whereIn('region_id', $regionIds);
            }
        }

        if (!empty($filters['neighborhood_id'])) {
            $region = \App\Models\Region::find($filters['neighborhood_id']);
            if ($region) {
                $regionIds = $region->getAllDescendantIds();
                $query->whereIn('region_id', $regionIds);
            }
        }

        if (!empty($filters['region_names'])) {
            $names = explode(',', $filters['region_names']);
            $regionIds = \App\Models\Region::whereIn('name', $names)->pluck('id')->toArray();
            $query->whereIn('region_id', $regionIds);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['min_area'])) {
            $query->where('area', '>=', $filters['min_area']);
        }

        if (!empty($filters['max_area'])) {
            $query->where('area', '<=', $filters['max_area']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('location', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (empty($filters['show_all'])) {
            $query->where('status', 'active');
        }

        $query->where('is_active', true);

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }
}
