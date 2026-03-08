<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Region;
use App\Models\ContactRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بهذه العملية'
                ], 403);
            }
            return $next($request);
        });
    }

    public function getUsers(Request $request)
    {
        $users = User::paginate(15);
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function getProperties(Request $request)
    {
        $query = Property::with(['owner', 'region']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $properties = $query->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    public function approveProperty(Request $request, $id)
    {
        $property = Property::find($id);
        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'العقار غير موجود'
            ], 404);
        }

        $property->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الموافقة على العقار بنجاح',
            'data' => $property
        ]);
    }

    public function rejectProperty(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $property = Property::find($id);
        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'العقار غير موجود'
            ], 404);
        }

        $property->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض العقار بنجاح',
            'data' => $property
        ]);
    }

    public function getContactRequests()
    {
        $requests = ContactRequest::with(['property', 'owner'])->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function createRegion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:country,governorate,city,neighborhood',
            'parent_id' => 'nullable|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $region = Region::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المنطقة بنجاح',
            'data' => $region
        ], 201);
    }

    public function updateRegion(Request $request, $id)
    {
        $region = Region::find($id);
        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'المنطقة غير موجودة'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:country,governorate,city,neighborhood',
            'parent_id' => 'nullable|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $region->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المنطقة بنجاح',
            'data' => $region
        ]);
    }

    public function deleteRegion($id)
    {
        $region = Region::find($id);
        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'المنطقة غير موجودة'
            ], 404);
        }

        $region->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنطقة بنجاح'
        ]);
    }

    public function statistics()
    {
        $usersCount = User::count();
        $propertiesCount = Property::count();
        $approvedProperties = Property::where('status', 'approved')->count();
        $pendingProperties = Property::where('status', 'pending')->count();
        $contactRequestsCount = ContactRequest::count();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $usersCount,
                'properties' => $propertiesCount,
                'approved_properties' => $approvedProperties,
                'pending_properties' => $pendingProperties,
                'contact_requests' => $contactRequestsCount,
            ]
        ]);
    }
}
