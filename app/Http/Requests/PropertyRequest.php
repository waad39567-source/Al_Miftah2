<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $method = $this->route()->getActionMethod();

        return match ($method) {
            'store' => [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'type' => 'required|in:sale,rent',
                'property_type' => 'required|string|max:100',
                'area' => 'required|numeric|min:1',
                'region_id' => 'required|exists:regions,id',
                'location' => 'required|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ],
            'update' => [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'type' => 'sometimes|in:sale,rent',
                'property_type' => 'sometimes|string|max:100',
                'area' => 'sometimes|numeric|min:1',
                'region_id' => 'sometimes|exists:regions,id',
                'location' => 'sometimes|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'title.required' => 'العنوان مطلوب',
            'title.max' => 'العنوان يجب ألا يتجاوز 255 حرف',
            'description.required' => 'الوصف مطلوب',
            'price.required' => 'السعر مطلوب',
            'price.numeric' => 'السعر يجب أن يكون رقماً',
            'price.min' => 'السعر يجب أن يكون موجباً',
            'type.required' => 'النوع مطلوب',
            'type.in' => 'النوع يجب أن يكون sale أو rent',
            'property_type.required' => 'نوع العقار مطلوب',
            'area.required' => 'المساحة مطلوبة',
            'area.numeric' => 'المساحة يجب أن تكون رقماً',
            'region_id.required' => 'المنطقة مطلوبة',
            'region_id.exists' => 'المنطقة غير موجودة',
            'location.required' => 'الموقع مطلوب',
        ];
    }
}
