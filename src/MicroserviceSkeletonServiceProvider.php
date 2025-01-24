<?php

namespace Kesify\MicroserviceSkeleton;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Kesify\MicroserviceSkeleton\Http\Middleware\JsonResponse;
use Kesify\MicroserviceSkeleton\Http\Middleware\LanguageMiddleware;
use Kesify\MicroserviceSkeleton\Http\Middleware\SetOrganization;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Kesify\MicroserviceSkeleton\Models\OrganizationUser;
use Kesify\MicroserviceSkeleton\Services\OrganizationService;

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

        $this->app->singleton('OrganizationService', function () {
            return new OrganizationService();
        });

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
    public function boot(): void
    {

        $this->publishes([
            __DIR__ . '/Config/microservice.php' => $this->app->configPath('microservice.php'),
            __DIR__ . '/routes/api.php' => $this->app->basePath('routes/api.php'),
        ], 'microservice-skeleton');

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
        $router->aliasMiddleware('SetOrganization', SetOrganization::class);
        $router->aliasMiddleware('LanguageMiddleware', LanguageMiddleware::class);
        $router->aliasMiddleware('JsonResponse', JsonResponse::class);
    }

    protected function addDatabaseConnection(): void
    {
        Config::set('database.connections.organization', Config::get('microservice.organization_connection'));
        Config::set('database.connections.main', Config::get('microservice.mainconnection'));
    }
}
