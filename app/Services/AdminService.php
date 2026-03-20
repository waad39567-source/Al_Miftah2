<?php

namespace App\Services;

use App\Models\User;
use App\Models\Property;
use App\Models\Region;
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

    public function getRecentActivities(int $limit = 10): array
    {
        $recentProperties = Property::with('owner')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'owner' => $property->owner ? $property->owner->name : null,
                    'type' => $property->type,
                    'status' => $property->status,
                    'price' => $property->price,
                    'created_at' => $property->created_at,
                ];
            });

        $recentContactRequests = ContactRequest::with(['user', 'property'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user_name' => $request->user ? $request->user->name : $request->name,
                    'property_title' => $request->property ? $request->property->title : null,
                    'status' => $request->status,
                    'message' => $request->message,
                    'created_at' => $request->created_at,
                ];
            });

        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'is_verified' => !is_null($user->email_verified_at),
                    'created_at' => $user->created_at,
                ];
            });

        return [
            'properties' => $recentProperties,
            'contact_requests' => $recentContactRequests,
            'users' => $recentUsers,
        ];
    }

    public function getChartData(): array
    {
        $totalProperties = Property::count();
        
        $propertiesByRegion = Property::with('region')
            ->get()
            ->groupBy('region_id')
            ->map(function ($items, $regionId) use ($totalProperties) {
                $region = $items->first()->region;
                $count = $items->count();
                return [
                    'region_id' => $regionId,
                    'region_name' => $region ? $region->name : 'غير محدد',
                    'count' => $count,
                    'percentage' => $totalProperties > 0 ? round(($count / $totalProperties) * 100, 1) : 0,
                ];
            })
            ->values()
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->toArray();

        $propertiesByType = Property::get()
            ->groupBy('property_type')
            ->map(function ($items, $type) use ($totalProperties) {
                $count = $items->count();
                return [
                    'property_type' => $type,
                    'label' => $this->getPropertyTypeLabel($type),
                    'count' => $count,
                    'percentage' => $totalProperties > 0 ? round(($count / $totalProperties) * 100, 1) : 0,
                ];
            })
            ->values()
            ->sortByDesc('count')
            ->toArray();

        $propertiesByStatus = Property::get()
            ->groupBy('status')
            ->map(function ($items, $status) use ($totalProperties) {
                $count = $items->count();
                return [
                    'status' => $status,
                    'label' => $this->getStatusLabel($status),
                    'count' => $count,
                    'percentage' => $totalProperties > 0 ? round(($count / $totalProperties) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();

        $totalUsers = User::count();
        $usersByRole = User::get()
            ->groupBy('role')
            ->map(function ($items, $role) use ($totalUsers) {
                $count = $items->count();
                return [
                    'role' => $role,
                    'label' => $this->getRoleLabel($role),
                    'count' => $count,
                    'percentage' => $totalUsers > 0 ? round(($count / $totalUsers) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();

        return [
            'properties_by_region' => $propertiesByRegion,
            'properties_by_type' => $propertiesByType,
            'properties_by_status' => $propertiesByStatus,
            'users_by_role' => $usersByRole,
        ];
    }

    public function getPropertiesByRegion(): array
    {
        $regions = Region::whereIn('type', ['city', 'governorate'])
            ->get();

        return $regions->map(function ($region) {
            $properties = Property::where('region_id', $region->id)->get();
            $total = $properties->count();
            $avgPrice = $total > 0 ? round($properties->avg('price')) : 0;

            return [
                'region_id' => $region->id,
                'region_name' => $region->name,
                'region_type' => $region->type,
                'total_properties' => $total,
                'active' => $properties->where('status', 'active')->count(),
                'pending' => $properties->where('status', 'pending')->count(),
                'rented' => $properties->where('status', 'rented')->count(),
                'sold' => $properties->where('status', 'sold')->count(),
                'rejected' => $properties->where('status', 'rejected')->count(),
                'avg_price' => $avgPrice,
            ];
        })->filter(function ($region) {
            return $region['total_properties'] > 0;
        })->values()->toArray();
    }

    public function getPropertiesByType(): array
    {
        $propertyTypes = Property::select('property_type')
            ->distinct()
            ->pluck('property_type');

        return $propertyTypes->map(function ($type) {
            $properties = Property::where('property_type', $type)->get();
            $total = $properties->count();

            return [
                'property_type' => $type,
                'label' => $this->getPropertyTypeLabel($type),
                'total' => $total,
                'active' => $properties->where('status', 'active')->count(),
                'pending' => $properties->where('status', 'pending')->count(),
                'rented' => $properties->where('status', 'rented')->count(),
                'sold' => $properties->where('status', 'sold')->count(),
                'rejected' => $properties->where('status', 'rejected')->count(),
                'avg_price' => $total > 0 ? round($properties->avg('price')) : 0,
                'min_price' => $total > 0 ? $properties->min('price') : 0,
                'max_price' => $total > 0 ? $properties->max('price') : 0,
            ];
        })->toArray();
    }

    private function getPropertyTypeLabel(string $type): string
    {
        $labels = [
            'apartment' => 'شقة',
            'villa' => 'فيلا',
            'house' => 'منزل',
            'land' => 'أرض',
            'shop' => 'متجر',
            'office' => 'مكتب',
            'warehouse' => 'مستودع',
            'building' => 'عمارة',
        ];
        return $labels[$type] ?? $type;
    }

    private function getStatusLabel(string $status): string
    {
        $labels = [
            'active' => 'نشط',
            'pending' => 'معلق',
            'rented' => 'مؤجر',
            'sold' => 'مباع',
            'rejected' => 'مرفوض',
        ];
        return $labels[$status] ?? $status;
    }

    private function getRoleLabel(string $role): string
    {
        $labels = [
            'admin' => 'مدير',
            'owner' => 'مكتب عقاري',
            'user' => 'مستخدم',
        ];
        return $labels[$role] ?? $role;
    }


    public function getPropertiesSummary(?string $fromDate = null, ?string $toDate = null, string $type = 'all'): array
    {
        $query = Property::query();

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate . ' 23:59:59');
        }

        if ($type === 'sold') {
            $query->where('status', 'sold');
        } elseif ($type === 'rented') {
            $query->where('status', 'rented');
        }

        $total = $query->count();
        $totalPrice = $query->sum('price');
        $avgPrice = $total > 0 ? round($totalPrice / $total) : 0;

        $byRegion = Property::select('region_id')
            ->where(function ($q) use ($fromDate, $toDate, $type) {
                if ($fromDate) {
                    $q->where('created_at', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->where('created_at', '<=', $toDate . ' 23:59:59');
                }
                if ($type === 'sold') {
                    $q->where('status', 'sold');
                } elseif ($type === 'rented') {
                    $q->where('status', 'rented');
                }
            })
            ->with('region')
            ->get()
            ->groupBy('region_id')
            ->map(function ($items, $regionId) {
                $region = $items->first()->region;
                return [
                    'region_id' => $regionId,
                    'region_name' => $region ? $region->name : 'غير محدد',
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->toArray();

        return [
            'total_count' => $total,
            'total_price' => (float) $totalPrice,
            'avg_price' => $avgPrice,
            'by_region' => $byRegion,
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'type' => $type,
            ],
        ];
    }

    public function getUsersRegistration(string $period = 'all', ?string $fromDate = null, ?string $toDate = null): array
    {
        $query = User::query();

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate . ' 23:59:59');
        }

        $total = $query->count();

        if ($period === 'daily') {
            $data = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where(function ($q) use ($fromDate, $toDate) {
                    if ($fromDate) {
                        $q->where('created_at', '>=', $fromDate);
                    }
                    if ($toDate) {
                        $q->where('created_at', '<=', $toDate . ' 23:59:59');
                    }
                })
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'count' => $item->count,
                    ];
                })
                ->toArray();
        } elseif ($period === 'weekly') {
            $data = User::selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count')
                ->where(function ($q) use ($fromDate, $toDate) {
                    if ($fromDate) {
                        $q->where('created_at', '>=', $fromDate);
                    }
                    if ($toDate) {
                        $q->where('created_at', '<=', $toDate . ' 23:59:59');
                    }
                })
                ->groupBy('year', 'week')
                ->orderBy('year', 'desc')
                ->orderBy('week', 'desc')
                ->limit(12)
                ->get()
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'week' => $item->week,
                        'count' => $item->count,
                    ];
                })
                ->toArray();
        } else {
            $data = [];
        }

        $byRole = User::select('role')
            ->where(function ($q) use ($fromDate, $toDate) {
                if ($fromDate) {
                    $q->where('created_at', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->where('created_at', '<=', $toDate . ' 23:59:59');
                }
            })
            ->get()
            ->groupBy('role')
            ->map(function ($items, $role) {
                return [
                    'role' => $role,
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->toArray();

        return [
            'total' => $total,
            'period' => $period,
            'by_date' => $data,
            'by_role' => $byRole,
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ];
    }

    public function getTopActiveRegions(int $limit = 10): array
    {
        $regions = Region::whereIn('type', ['city', 'governorate'])
            ->withCount([
                'properties as total_properties' => function ($query) {
                    $query->where('status', '!=', 'rejected');
                },
                'properties as active_properties' => function ($query) {
                    $query->where('status', 'active');
                },
                'properties as pending_properties' => function ($query) {
                    $query->where('status', 'pending');
                },
                'properties as sold_properties' => function ($query) {
                    $query->where('status', 'sold');
                },
                'properties as rented_properties' => function ($query) {
                    $query->where('status', 'rented');
                },
            ])
            ->orderBy('total_properties', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($region) {
                $avgPrice = Property::where('region_id', $region->id)
                    ->where('status', '!=', 'rejected')
                    ->avg('price') ?? 0;

                return [
                    'region_id' => $region->id,
                    'region_name' => $region->name,
                    'region_type' => $region->type,
                    'total_properties' => $region->total_properties ?? 0,
                    'active_properties' => $region->active_properties ?? 0,
                    'pending_properties' => $region->pending_properties ?? 0,
                    'sold_properties' => $region->sold_properties ?? 0,
                    'rented_properties' => $region->rented_properties ?? 0,
                    'avg_price' => round($avgPrice),
                ];
            })
            ->toArray();

        return $regions;
    }


}
