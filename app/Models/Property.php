<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'owner_id','title','description','price','currency',
        'type','property_type','area','region_id',
        'location','latitude','longitude',
        'status','rejection_reason',
        'is_active','approved_by','approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'price' => 'float',
        'area' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function contactRequests()
    {
        return $this->hasMany(ContactRequest::class);
    }

    public static function getStatuses(): array
    {
        return [
            'pending' => 'بانتظار الموافقة',
            'active' => 'نشط',
            'rented' => 'مؤجر',
            'sold' => 'مباع',
            'rejected' => 'مرفوض',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
