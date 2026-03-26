<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^(\+?963|09)[0-9]{8}$/'],
            'message' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'property_id.required' => 'معرف العقار مطلوب',
            'property_id.exists' => 'العقار غير موجود',
            'name.required' => 'الاسم مطلوب',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex' => 'رقم الهاتف يجب أن يكون رقم سوري صحيح (09XXXXXXXX أو +963XXXXXXXX)',
        ];
    }
}
