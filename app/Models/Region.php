<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name', 'type', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public static function getTypes(): array
    {
        return [
            'country' => 'دولة',
            'governorate' => 'محافظة',
            'city' => 'مدينة',
            'neighborhood' => 'حي',
        ];
    }
}