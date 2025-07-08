<?php

namespace Kesify\MicroserviceSkeleton;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Kesify\MicroserviceSkeleton\Http\Middleware\JsonResponse;
use Kesify\MicroserviceSkeleton\Http\Middleware\LanguageMiddleware;
use Kesify\MicroserviceSkeleton\Http\Middleware\SetOrganization;
use Kesify\MicroserviceSkeleton\Http\Middleware\CheckTokenValidity;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Kesify\MicroserviceSkeleton\Models\OrganizationUser;
use Kesify\MicroserviceSkeleton\Services\FileStorageService;
use Kesify\MicroserviceSkeleton\Services\KeyService;
use Kesify\MicroserviceSkeleton\Services\NumberRangesService;
use Kesify\MicroserviceSkeleton\Services\OrganizationService;

class MicroserviceSkeletonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->bindServices();
        $this->registerCommands();
        $this->mergeConfig();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishAssets();
        $this->addDatabaseConnection();
        $this->addFilesystemDisks();
        $this->registerController();
        $this->registerMiddleware();
    }

    /**
     * Bind services into the container.
     *
     * @return void
     */
    protected function bindServices(): void
    {
        $this->app->bind('Organization', Organization::class);
        $this->app->bind('OrganizationUser', OrganizationUser::class);

        $this->app->singleton('OrganizationService', function () {
            return new OrganizationService();
        });

        $this->app->singleton('FileStorageService', function () {
            return new FileStorageService();
        });

        $this->app->singleton('KeyService', function () {
            return new KeyService();
        });

        $this->app->singleton('NumberRangesService', function () {
            return new NumberRangesService();
        });
    }

    /**
     * Register artisan commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Kesify\MicroserviceSkeleton\Console\Commands\MigrateOrganization::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\RollbackOrganization::class,
            \Kesify\MicroserviceSkeleton\Console\Commands\SeedOrganization::class,
        ]);
    }

    /**
     * Merge package configuration files.
     *
     * @return void
     */
    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/microservice.php', 'microservice');
    }

    /**
     * Publish configuration files and routes.
     *
     * @return void
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/Config/microservice.php' => $this->app->configPath('microservice.php'),
            __DIR__ . '/Config/filestorage.php' => $this->app->configPath('filestorage.php'),
            __DIR__ . '/routes/api.php' => $this->app->basePath('routes/api.php'),
            __DIR__ . '/.env.microservice-example' => $this->app->basePath('.env.microservice-example'),
            __DIR__ . '/migrations/add_microservice.php' => $this->app->basePath('/database/migrations/add_microservice.php'),
            __DIR__ . '/migrations/add_module.php' => $this->app->basePath('/database/migrations/add_module.php'),
            __DIR__ . '/lang' => $this->app->basePath('resources/lang'),
        ], 'microservice-skeleton');
    }

    /**
     * Add custom database connections.
     *
     * @return void
     */
    protected function addDatabaseConnection(): void
    {
        Config::set('database.connections.main', Config::get('microservice.database.connections.main'));
        Config::set('database.connections.organization', Config::get('microservice.database.connections.organization'));
    }


    /**
     * Add custom filesystem0 disks.
     *
     * @return void
     */
    protected function addFilesystemDisks(): void
    {
        Config::set('filesystems.disks.s3-private', Config::get('microservice.filesystems.disks.s3-private'));
        Config::set('filesystems.disks.s3-public', Config::get('microservice.filesystems.disks.s3-public'));
    }


    /**
     * Register middleware for the package.
     *
     * @throws BindingResolutionException
     * @return void
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('SetOrganization', SetOrganization::class);
        $router->aliasMiddleware('LanguageMiddleware', LanguageMiddleware::class);
        $router->aliasMiddleware('JsonResponse', JsonResponse::class);
    }

    protected function registerController(): void
    {
        $this->app->make('Kesify\MicroserviceSkeleton\Http\Controllers\MicroserviceController');
    }
}
