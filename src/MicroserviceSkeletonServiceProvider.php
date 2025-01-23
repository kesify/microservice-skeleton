<?php

namespace Kesify\MicroserviceSkeleton;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Kesify\MicroserviceSkeleton\Http\Middleware\SetOrganization;

class MicroserviceSkeletonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            \Kesify\MicroserviceSkeleton\Console\Commands\AddEnvVariables::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\MigrateOrganization::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\RollbackOrganization::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\SeedOrganization::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Middleware registrieren
        $this->registerMiddleware();
        $this->addDatabaseConnection();
    }

    /**
     * Register middleware for the package.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        // Middleware alias hinzufügen
        $router->aliasMiddleware('setOrganization', SetOrganization::class);
    }

    protected function addDatabaseConnection(): void
    {
        Config::set('database.connections.organization', [
                'driver' => 'mysql',
                'host' => env('ORGANIZATION_DB_HOST', '127.0.0.1'),
                'port' => env('ORGANIZATION_DB_PORT', '3306'),
                'database' => env('ORGANIZATION_DB_NAME', 'organization_db'),
                'username' => env('ORGANIZATION_DB_USERNAME', 'root'),
                'password' => env('ORGANIZATION_DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]
        );
    }
}
