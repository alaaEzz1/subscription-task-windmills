<?php

namespace Modules\Subscription\src\Providers;

use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register bindings here
        $this->app->register(\Modules\Subscription\src\Providers\AuthServiceProvider::class);
    }

    public function boot()
    {
        $modulePath = base_path('Modules/Subscription');

        // Load routes
        $this->loadRoutesFrom("$modulePath/routes/web.php");

        // Load views
        $this->loadViewsFrom("$modulePath/resources/views", 'Subscription');

        // Load migrations
        $this->loadMigrationsFrom("$modulePath/database/migrations");
    }
}
