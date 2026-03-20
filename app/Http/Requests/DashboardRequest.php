<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'recentActivities' => [
                'limit' => 'nullable|integer|min:1|max:100',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'limit.integer' => 'الحد يجب أن يكون رقماً',
            'limit.min' => 'الحد يجب أن يكون أكبر من صفر',
            'limit.max' => 'الحد يجب أن لا يتجاوز 100',
        ];
    }
}
