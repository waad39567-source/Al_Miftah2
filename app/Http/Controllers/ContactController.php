<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $property = Property::find($request->property_id);

        $contactRequest = ContactRequest::create([
            'property_id' => $request->property_id,
            'owner_id' => $property->owner_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب التواصل بنجاح',
            'data' => $contactRequest
        ], 201);
    }
}
