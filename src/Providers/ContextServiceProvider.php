<?php

namespace Kesify\MicroserviceSkeleton\Providers;

use Illuminate\Support\ServiceProvider;
use Kesify\MicroserviceSkeleton\Support\CurrentOrganization;

class ContextServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentOrganization::class, fn($app) =>
            new CurrentOrganization($app['request'])
        );
    }
}
