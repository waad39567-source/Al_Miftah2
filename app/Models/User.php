<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'is_active',
        'is_banned',
        'banned_at',
        'ban_reason',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_banned' => 'boolean',
    ];

    protected $appends = ['is_admin'];

    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // العقارات التي يملكها
    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    // العقارات التي وافق عليها (admin)
    public function approvedProperties()
    {
        return $this->hasMany(Property::class, 'approved_by');
    }

    // طلبات التواصل
    public function contactRequests()
    {
        return $this->hasMany(ContactRequest::class);
    }

    // الإشعارات
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (اختياري احترافي)
    |--------------------------------------------------------------------------
    */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }
}