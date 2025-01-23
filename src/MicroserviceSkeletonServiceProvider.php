<?php

namespace Kesify\MicroserviceSkeleton;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Kesify\MicroserviceSkeleton\Http\Middleware\SetOrganization;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Kesify\MicroserviceSkeleton\Models\OrganizationUser;

class MicroserviceSkeletonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Organization', Organization::class);
        $this->app->bind('OrganizationUser', OrganizationUser::class);

        $this->commands([
            \Kesify\MicroserviceSkeleton\Console\Commands\AddEnvVariables::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\MigrateOrganization::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\RollbackOrganization::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\SeedOrganization::class,
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/Config/microservice.php',
            'microservice'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__ . '/Config/microservice.php' => $this->app->configPath('microservice.php'),
        ], 'microservice-config');

        // Middleware registrieren
        $this->registerMiddleware();
        $this->addDatabaseConnection();
    }

    /**
     * Register middleware for the package.
     * @throws BindingResolutionException
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        // Middleware alias hinzufügen
        $router->aliasMiddleware('setOrganization', SetOrganization::class);
    }

    protected function addDatabaseConnection(): void
    {
        Config::set('database.connections.organization', Config::get('microservice.organization_connection'));
    }
}
