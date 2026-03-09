<?php

namespace App\Services;

use App\Models\ContactRequest as ContactRequestModel;
use App\Models\Property;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactService
{
    public function create(array $data): ContactRequestModel
    {
        $property = Property::findOrFail($data['property_id']);

        return ContactRequestModel::create([
            'property_id' => $data['property_id'],
            'owner_id' => $property->owner_id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'message' => $data['message'] ?? null,
        ]);
    }

    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = ContactRequestModel::with(['property', 'owner']);

        if (!empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }

        if (!empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function getById(int $id): ?ContactRequestModel
    {
        return ContactRequestModel::with(['property', 'owner'])->find($id);
    }
}
