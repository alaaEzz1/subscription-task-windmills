<?php

namespace Modules\Subscription\src\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Subscription\src\Models\Subscription;
use Modules\Subscription\src\Policies\SubscriptionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Subscription::class => SubscriptionPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
