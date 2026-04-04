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
                'name'     => 'required|string|max:255',
                'email'    => 'nullable|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone'    => 'required|string|min:8|max:20|unique:users,phone',
            ],
            'login' => [
                'login'    => 'required|string|min:7|max:50',
                'password' => 'required|string',
            ],
            'changePassword' => [
                'current_password' => 'required|string',
                'password'         => 'required|string|min:8|confirmed',
            ],
            'setFirebasePassword' => [
                'id_token'  => 'required|string',
                'password'  => 'required|string|min:8|confirmed',
            ],
            'promoteToAdmin' => [
                'id'    => 'nullable|integer|exists:users,id',
                'email' => 'nullable|string|email|exists:users,email',
            ],
            'createUser' => [
                'name'      => 'required|string|max:255',
                'email'     => 'nullable|string|email|max:255|unique:users',
                'password'  => 'required|string|min:8|confirmed',
                'phone'     => 'required|string|min:8|max:20',
                'role'      => 'nullable|in:user,admin',
                'is_active' => 'nullable|boolean',
            ],
            'updateProfile' => [
                'name'  => 'sometimes|string|max:255',
                'email' => 'sometimes|nullable|string|email|max:255|unique:users,email',
                'phone' => 'sometimes|string|min:8|max:20',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'الاسم مطلوب',
            'name.string'               => 'الاسم يجب أن يكون نصاً',
            'name.max'                  => 'الاسم يجب ألا يتجاوز 255 حرف',
            'email.email'               => 'البريد الإلكتروني غير صالح',
            'email.unique'              => 'البريد الإلكتروني مستخدم مسبقاً',
            'email.max'                 => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرف',
            'password.required'         => 'كلمة المرور مطلوبة',
            'password.min'              => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed'        => 'تأكيد كلمة المرور غير متطابق',
            'phone.required'            => 'رقم الهاتف مطلوب',
            'phone.min'                 => 'رقم الهاتف قصير جداً',
            'phone.max'                 => 'رقم الهاتف طويل جداً',
            'phone.unique'              => 'رقم الهاتف مستخدم مسبقاً',
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'id_token.required'         => 'Firebase token مطلوب',
            'role.required'             => 'الدور مطلوب',
            'role.in'                   => 'قيمة الدور غير صالحة',
            'id.integer'                => 'المعرّف يجب أن يكون رقماً',
            'id.exists'                 => 'المستخدم غير موجود',
            'email.exists'              => 'المستخدم غير موجود',
        ];
    }
}
