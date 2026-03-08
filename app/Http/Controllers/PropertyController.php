<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['owner', 'region', 'images'])->where('is_active', true);

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->region_id) {
            $query->where('region_id', $request->region_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $properties = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    public function show($id)
    {
        $property = Property::with(['owner', 'region', 'images'])->find($id);
        
        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'العقار غير موجود'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $property
        ]);
    }
}
