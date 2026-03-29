<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $method = $this->route()->getActionMethod();

        return match ($method) {
            'register' => [
                'name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|string|min:8|max:20',
            ],
            'login' => [
                'email' => 'required|string|min:8|max:50',
                'password' => 'required|string',
            ],
            'changePassword' => [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ],
            'promoteToAdmin' => [
                'id' => 'nullable|integer|exists:users,id',
                'email' => 'nullable|string|email|exists:users,email',
            ],
            'createUser' => [
                'name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|string|min:8|max:20',
                'role' => 'nullable|in:user,admin',
                'is_active' => 'nullable|boolean',
            ],
            'updateProfile' => [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|nullable|string|email|max:255|unique:users,email',
                'phone' => 'sometimes|string|min:8|max:20',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            // name
            'name.required' => 'الاسم مطلوب',
            'name.string' => 'الاسم يجب أن يكون نص',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',

            // email
            'email.email' => 'البريد الإلكتروني غير صالح',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرف',

            // password
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'password.string' => 'كلمة المرور يجب أن تكون نص',

            // phone
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.string' => 'رقم الهاتف يجب أن يكون نص',
            'phone.min' => 'رقم الهاتف قصير جداً',
            'phone.max' => 'رقم الهاتف طويل جداً',
            'phone.unique' => 'رقم الهاتف مستخدم من قبل',

            // current password
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'current_password.string' => 'كلمة المرور الحالية يجب أن تكون نص',

            // role
            'role.required' => 'الدور مطلوب',
            'role.in' => 'الدور المحدد غير صالح',

            // id
            'id.integer' => 'المعرف يجب أن يكون رقم',
            'id.exists' => 'المستخدم غير موجود',

            // email exists
            'email.exists' => 'المستخدم غير موجود',
        ];
    }
}
