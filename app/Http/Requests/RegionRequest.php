<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegionRequest extends FormRequest
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
                'name' => 'required|string|max:255',
                'type' => 'required|in:country,governorate,city,neighborhood',
                'parent_id' => 'nullable|exists:regions,id',
            ],
            'update' => [
                'name' => 'sometimes|string|max:255',
                'type' => 'sometimes|in:country,governorate,city,neighborhood',
                'parent_id' => 'nullable|exists:regions,id',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'name.string' => 'الاسم يجب أن يكون نص',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',
            'type.required' => 'النوع مطلوب',
            'type.in' => 'النوع المحدد غير صالح',
            'parent_id.exists' => 'المنطقة الأب غير موجودة',
        ];
    }
}
