# Microservice Skeleton

**Microservice Skeleton** is a Laravel package designed to simplify the management of organizations and users by providing helpful models, attributes, and caching mechanisms.

---

## Requirements

- **PHP:** ^8.4
- **Laravel:** ^11
- **Dependencies:**
    - illuminate/support
    - illuminate/auth
    - illuminate/cache

---

## Installation

1. **Add the package as a local repository:**

   In the `composer.json` of your Laravel project:

   ```json
   "repositories": [
       {
           "type": "vcs",
           "url": "git@github.com:kesify/microservice-skeleton.git"
       }
   ]
   ```

2. **Install the package via Composer:**

   ```bash
   composer require kesify/microservice-skeleton
   ```

3. **Optional: Publish the configuration files and migrations:**

   ```bash
   php artisan vendor:publish --tag=microservice-skeleton
   ```

---

## Setup

1. **Update Environment Variables:**
   Copy `.env.microservice-example` and configure the database and Redis environment variables in your `.env` file.

2. **Remove `web.php` Routing:**
   Replace the web routing in `/bootstrap/app.php` with API routing:

   ```php
   api: __DIR__.'/../routes/api.php',
   ```

3. **Add Middleware in `/bootstrap/app.php`:**
   Append the following middleware classes:

   ```php
   $middleware->append(Kesify\MicroserviceSkeleton\Http\Middleware\JsonResponse::class);
   $middleware->append(Kesify\MicroserviceSkeleton\Http\Middleware\LanguageMiddleware::class);
   $middleware->append(Kesify\MicroserviceSkeleton\Http\Middleware\SetOrganization::class);
   ```
4. **Add in `/config/auth.php` `api`-Guard:**

   ```php
   'guards' => [
       'api' => [
           'driver' => 'passport',
           'provider' => 'users',
       ]
   ]
   ```
5. **Change in `/config/auth.php` `users` Provider:**

   ```php
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', \Kesify\MicroserviceSkeleton\Models\User::class),
        ],
    ],
   ```
6. **Copy in `/storage` folder oauth public and private key from gateway**

---

## Commands

The command namespace is `ms` and `organization`.

- **Add environment variables:**

   ```bash
   php artisan ms:add-env
   ```

- **Run organization migrations:**

   ```bash
   php artisan organization:migrate
   ```

- **Rollback organization migrations:**

   ```bash
   php artisan organization:rollback
   ```

- **Seed organization data:**

   ```bash
   php artisan organization:seed
   ```

---

## Tests

1. **Install development dependencies:**

   ```bash
   composer install --dev
   ```

2. **Run tests with PHPUnit:**

   ```bash
   ./vendor/bin/phpunit
   ```

---

## License

This package is licensed under the [MIT License](LICENSE).
