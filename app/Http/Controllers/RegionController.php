<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::with('children')->whereNull('parent_id')->get();
        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }

    public function show($id)
    {
        $region = Region::with(['children', 'parent'])->find($id);
        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'المنطقة غير موجودة'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $region
        ]);
    }
}
