<?php

namespace App\Providers;

use App\Models\ContactRequest;
use App\Models\Property;
use App\Models\Region;
use App\Models\User;
use App\Policies\ContactRequestPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\RegionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Property::class => PropertyPolicy::class,
        ContactRequest::class => ContactRequestPolicy::class,
        Region::class => RegionPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
