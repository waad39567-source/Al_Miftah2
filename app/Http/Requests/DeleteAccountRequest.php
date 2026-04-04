<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $isGoogleUser = $user && ($user->auth_provider === 'google' || is_null($user->password));

        return [
            'password' => [$isGoogleUser ? 'nullable' : 'required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'كلمة المرور مطلوبة للتأكيد',
        ];
    }
}
