# microservice-skeleton

**microservice-skeleton** is a Laravel package designed to simplify the management of organizations and users by providing helpful models, attributes, and caching mechanisms.

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

## Commands

The command namespace is `ms` and `organization`.

To add environment variables:

```bash
php artisan ms:add-env
```

To run organization migrations:

```bash
php artisan organization:migrate
```

To rollback organization migrations:

```bash
php artisan organization:rollback
```

To seed organization data:

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
