<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Pagination\LengthAwarePaginator;

class RegionService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Region::with('parent');

        if (!empty($filters['include_children'])) {
            $query->with('children');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (array_key_exists('parent_id', $filters)) {
            if ($filters['parent_id'] === 'null' || $filters['parent_id'] === '') {
                $query->whereNull('parent_id');
            } elseif ($filters['parent_id'] !== null) {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['has_children'])) {
            if ($filters['has_children'] === 'true' || $filters['has_children'] === '1') {
                $query->has('children');
            } else {
                $query->doesntHave('children');
            }
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function getById(int $id): ?Region
    {
        return Region::with(['parent', 'children', 'properties'])->find($id);
    }

    public function getByIdWithNestedChildren(int $id): ?Region
    {
        $region = Region::with('parent')->find($id);
        if ($region) {
            $region->load('children.children.children');
        }
        return $region;
    }

    public function create(array $data): Region
    {
        return Region::create($data);
    }

    public function update(Region $region, array $data): Region
    {
        $region->update($data);
        return $region->fresh();
    }

    public function delete(Region $region): bool
    {
        if ($region->children()->count() > 0) {
            return false;
        }

        if ($region->properties()->count() > 0) {
            return false;
        }

        return $region->delete();
    }

    public function getRootRegions()
    {
        return Region::whereNull('parent_id')->with('children')->get();
    }

    public function getRootRegionsWithNestedChildren()
    {
        return Region::whereNull('parent_id')
            ->with('children.children.children')
            ->get();
    }

    public function getChildren(int $id)
    {
        $region = Region::find($id);
        if (!$region) {
            return null;
        }
        return $region->children()->with('children')->get();
    }
}
