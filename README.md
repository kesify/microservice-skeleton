# microservice-skeleton

**microservice-skeleton** ist ein Laravel-Package, das die Verwaltung von Organisationen und Benutzern erleichtert, indem es hilfreiche Modelle, Attribute und Caching-Mechanismen bereitstellt.

---

## Anforderungen

- **PHP:** ^8.4
- **Laravel:** ^11
- **Abhängigkeiten:**
    - illuminate/support
    - illuminate/auth
    - illuminate/cache

---

## Installation

1. **Installiere das Package über Composer:**

   ```bash
   composer require kesify/microservice-skeleton
   ```

2. **Optional: Veröffentliche die Konfigurationsdateien und Migrationen:**

   ```bash
   php artisan vendor:publish --tag=microservice-skeleton-config
   ```
---

## Tests

1. **Installiere die Entwicklungsabhängigkeiten:**

   ```bash
   composer install --dev
   ```

2. **Führe die Tests mit PHPUnit aus:**

   ```bash
   ./vendor/bin/phpunit
   ```

---

## Entwicklung

Wenn du das Package lokal testen möchtest, kannst du es in einem Laravel-Projekt verwenden:

1. **Füge das Package als lokales Repository hinzu:**

   In der `composer.json` deines Laravel-Projekts:

   ```json
   "repositories": [
       {
           "type": "vcs",
           "url": "git@github.com:kesify/microservice-skeleton.git"
       }
   ]
   ```

2. **Installiere das Package:**

   ```bash
   composer require kesify/microservice-skeleton
   ```

---

## Lizenz

Dieses Package ist unter der [MIT-Lizenz](LICENSE) lizenziert.
