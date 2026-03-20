<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'propertiesSummary' => [
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'type' => 'nullable|in:sold,rented,all',
            ],
            'usersRegistration' => [
                'period' => 'nullable|in:daily,weekly,all',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
            ],
            'topActiveRegions' => [
                'limit' => 'nullable|integer|min:1|max:50',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'from_date.date' => 'تاريخ البداية يجب أن يكون تاريخاً صحيحاً',
            'to_date.date' => 'تاريخ النهاية يجب أن يكون تاريخاً صحيحاً',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
            'type.in' => 'النوع يجب أن يكون: sold, rented, أو all',
            'period.in' => 'الفترة يجب أن تكون: daily, weekly, أو all',
            'limit.integer' => 'الحد يجب أن يكون رقماً',
            'limit.min' => 'الحد يجب أن يكون أكبر من صفر',
            'limit.max' => 'الحد يجب أن لا يتجاوز 50',
        ];
    }
}
