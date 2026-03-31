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
        'email_verified_at',
        'firebase_uid',
        'auth_provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'firebase_uid',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'banned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id')->onDelete('cascade');
    }

    public function approvedProperties()
    {
        return $this->hasMany(Property::class, 'approved_by')->onDelete('cascade');
    }

    public function contactRequests()
    {
        return $this->hasMany(ContactRequest::class)->onDelete('cascade');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->onDelete('cascade');
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class)->onDelete('cascade');
    }

    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'property_favorites')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
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

    public function isFirebaseUser(): bool
    {
        return !is_null($this->firebase_uid);
    }
}
