<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $userId = $user?->id;
        $isGoogleUser = $user && ($user->auth_provider === 'google' || is_null($user->password));

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                "unique:users,email,{$userId}",
            ],
            'password' => [$isGoogleUser ? 'nullable' : 'required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صالح',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرف',
            'password.required' => 'كلمة المرور مطلوبة للتأكيد',
        ];
    }
}
