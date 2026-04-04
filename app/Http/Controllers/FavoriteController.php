<?php

namespace App\Http\Controllers;

use App\Models\PropertyFavorite;
use App\Models\Property;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;

        $favorites = PropertyFavorite::where('user_id', $request->user()->id)
            ->whereHas('property', fn($q) => $q->where('status', 'active'))
            ->with('property')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $properties = $favorites->pluck('property');

        return response()->json([
            'success' => true,
            'data' => [
                'data' => PropertyResource::collection($properties),
                'pagination' => [
                    'current_page' => $favorites->currentPage(),
                    'last_page' => $favorites->lastPage(),
                    'per_page' => $favorites->perPage(),
                    'total' => $favorites->total(),
                ],
            ],
        ]);
    }

    public function store(Request $request, $propertyId)
    {
        $property = Property::find($propertyId);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'العقار غير موجود',
            ], 404);
        }

        $exists = PropertyFavorite::where('user_id', $request->user()->id)
            ->where('property_id', $propertyId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'العقار موجود بالفعل في المفضلة',
            ], 400);
        }

        $favorite = PropertyFavorite::create([
            'user_id' => $request->user()->id,
            'property_id' => $propertyId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة العقار إلى المفضلة',
            'data' => $favorite,
        ], 201);
    }

    public function destroy(Request $request, $propertyId)
    {
        $favorite = PropertyFavorite::where('user_id', $request->user()->id)
            ->where('property_id', $propertyId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'العقار غير موجود في المفضلة',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العقار من المفضلة',
        ]);
    }

    public function check(Request $request, $propertyId)
    {
        $exists = PropertyFavorite::where('user_id', $request->user()->id)
            ->where('property_id', $propertyId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => ['is_favorite' => $exists],
        ]);
    }
}
