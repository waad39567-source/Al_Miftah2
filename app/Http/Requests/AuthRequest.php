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
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => ['required', 'string', 'regex:/^(\+?963|09)[0-9]{8}$/'],
                'role' => 'required|in:user,owner',
            ],
            'login' => [
                'email' => 'required|string|email',
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
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => ['nullable', 'string', 'regex:/^(\+?963|09)[0-9]{8}$/'],
                'role' => 'required|in:user,owner,admin',
                'is_active' => 'nullable|boolean',
            ],
            'updateProfile' => [
                'name' => 'sometimes|string|max:255',
                'phone' => ['sometimes', 'string', 'regex:/^09[0-9]{8}$/'],
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
            'email.required' => 'البريد الإلكتروني مطلوب',
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
            'phone.regex' => 'رقم الهاتف يجب أن يكون رقم سوري صحيح (09XXXXXXXX أو +963XXXXXXXX)',
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
