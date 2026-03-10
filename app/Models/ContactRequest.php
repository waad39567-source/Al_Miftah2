<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'owner_id',
        'name',
        'phone',
        'message',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function getStatuses(): array
    {
        return [
            'pending' => 'معلق',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
        ];
    }
}
