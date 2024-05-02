# Laravel Settings

[![Tests](https://github.com/philiprehberger/laravel-settings/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/laravel-settings/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/laravel-settings.svg)](https://packagist.org/packages/philiprehberger/laravel-settings)
[![Last updated](https://img.shields.io/github/last-commit/philiprehberger/laravel-settings)](https://github.com/philiprehberger/laravel-settings/commits/main)

Type-safe, cached application settings stored in the database with a simple key-value API.

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require philiprehberger/laravel-settings
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag=settings-migrations
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=settings-config
```

### Configuration

`config/settings.php`:

```php
return [
    'table' => 'settings',

    'cache' => [
        'enabled' => true,
        'key'     => 'app_settings',
        'ttl'     => 3600,          // seconds; null = forever
    ],

    'defaults' => [
        // 'app.timezone' => 'UTC',
    ],
];
```

## Usage

```php
use PhilipRehberger\Settings\Facades\Settings;

// Store a value (type is auto-detected)
Settings::set('app.name', 'My Portal');
Settings::set('pagination.per_page', 25);
Settings::set('feature.dark_mode', true);
Settings::set('allowed.ips', ['127.0.0.1', '10.0.0.1']);

// Retrieve
Settings::get('app.name');                         // 'My Portal'
Settings::get('missing.key');                      // null
Settings::get('missing.key', 'fallback');          // 'fallback'

// Explicit type override
Settings::set('items.count', '10', 'int');         // stored and retrieved as int

// Check existence
Settings::has('app.name');                         // true

// Remove
Settings::forget('app.name');

// Get all settings
Settings::all();                                   // Collection<string, mixed>

// Get all settings in a group (keys prefixed with 'mail.')
Settings::all('mail');

// Remove everything
Settings::flush();
```

### Increment / Decrement

```php
Settings::set('login.count', 0);

Settings::increment('login.count');          // 1
Settings::increment('login.count', 5);       // 6
Settings::decrement('login.count', 2);       // 4

// Works with floats
Settings::set('balance', 10.0);
Settings::increment('balance', 1.5);         // 11.5

// Creates the key if it doesn't exist
Settings::increment('new.counter');           // 1
Settings::decrement('new.gauge');             // -1
```

### Bulk Operations

```php
// Set multiple values at once
Settings::setMany([
    'app.name'   => 'My Portal',
    'app.locale' => 'en',
    'app.debug'  => false,
]);

// Get multiple values at once
$values = Settings::getMany(['app.name', 'app.locale', 'app.debug']);
// ['app.name' => 'My Portal', 'app.locale' => 'en', 'app.debug' => false]
```

### Type Casting

Values are automatically cast back to their original type on retrieval.

| PHP type   | `type` column | Round-trips correctly |
|------------|---------------|-----------------------|
| `string`   | `string`      | Yes                   |
| `int`      | `int`         | Yes                   |
| `float`    | `float`       | Yes                   |
| `bool`     | `bool`        | Yes (`true`/`false`)  |
| `array`    | `array`       | Yes (JSON encoded)    |
| — explicit | `json`        | Yes (JSON encoded)    |

```php
Settings::set('enabled', true);
Settings::get('enabled'); // (bool) true

Settings::set('rate', 0.19);
Settings::get('rate'); // (float) 0.19

Settings::set('tags', ['php', 'laravel']);
Settings::get('tags'); // (array) ['php', 'laravel']
```

### Default Fallback Chain

When a key is not found in the database, `Settings::get()` resolves in this order:

1. `config('settings.defaults.<key>')` — static config defaults
2. The `$default` argument passed to `get()`

```php
// config/settings.php
'defaults' => [
    'app.timezone' => 'UTC',
],

// Returns 'UTC' even if nothing is stored in the DB
Settings::get('app.timezone', 'Europe/London');
```

### Group Filtering

A "group" is everything before the first dot in the key name.

```php
Settings::set('mail.host', 'smtp.example.com');
Settings::set('mail.port', 587);
Settings::set('app.name', 'My App');

Settings::all('mail');
// Collection {
//   'mail.host' => 'smtp.example.com',
//   'mail.port' => 587,
// }
```

### Per-User Settings

Each user's settings are stored with a `user_id` and cached independently.

```php
Settings::setForUser($userId, 'theme', 'dark');
Settings::getForUser($userId, 'theme');         // 'dark'
Settings::hasForUser($userId, 'theme');         // true
Settings::forgetForUser($userId, 'theme');
Settings::allForUser($userId);
Settings::allForUser($userId, 'mail');          // filtered by group
Settings::flushForUser($userId);
```

Per-user settings are completely isolated from global settings. Two users can have different values for the same key, and both are independent from any global value stored without a `user_id`.

### Cache

All settings are cached as a single serialized `Collection` under one key (default: `app_settings`). This means every read after the first is served from the cache. Any write (`set`, `forget`, `flush`) immediately invalidates the cache so the next read re-hydrates from the database.

Disable caching entirely in `config/settings.php`:

```php
'cache' => [
    'enabled' => false,
],
```

### Artisan Commands

```bash
# List all settings
php artisan settings:list

# List settings in a group
php artisan settings:list --group=mail

# Get a single setting
php artisan settings:get app.name

# Set a setting (type auto-detected)
php artisan settings:set app.name "My Portal"

# Set with explicit type
php artisan settings:set pagination.per_page 25 --type=int
php artisan settings:set tax.rate 0.19 --type=float
php artisan settings:set feature.enabled true --type=bool
php artisan settings:set allowed.ips '["127.0.0.1"]' --type=array
```

### Database Schema

| Column       | Type            | Notes                                  |
|--------------|-----------------|----------------------------------------|
| `id`         | bigint unsigned | Primary key                            |
| `key`        | varchar         | Unique per `user_id` scope             |
| `value`      | text            | Serialized value                       |
| `type`       | varchar(20)     | `string|int|float|bool|array|json`     |
| `group`      | varchar(100)    | Nullable, indexed, derived from key    |
| `user_id`    | bigint unsigned | Nullable, indexed, for per-user scopes |
| `created_at` | timestamp       |                                        |
| `updated_at` | timestamp       |                                        |

## API

| Method | Description |
|--------|-------------|
| `Settings::set(string $key, mixed $value, ?string $type)` | Store a value (type auto-detected) |
| `Settings::get(string $key, mixed $default)` | Retrieve a value with optional default |
| `Settings::has(string $key): bool` | Check if a key exists |
| `Settings::forget(string $key)` | Remove a key |
| `Settings::all(?string $group): Collection` | Get all settings, optionally filtered by group |
| `Settings::flush()` | Remove all settings |
| `Settings::getMany(array $keys): array` | Retrieve multiple settings at once |
| `Settings::setMany(array $values): void` | Store multiple settings at once |
| `Settings::increment(string $key, int\|float $amount): int\|float` | Increment a numeric setting |
| `Settings::decrement(string $key, int\|float $amount): int\|float` | Decrement a numeric setting |
| `Settings::setForUser(int $userId, string $key, mixed $value)` | Store a per-user value |
| `Settings::getForUser(int $userId, string $key, mixed $default)` | Retrieve a per-user value |
| `Settings::hasForUser(int $userId, string $key): bool` | Check per-user key existence |
| `Settings::forgetForUser(int $userId, string $key)` | Remove a per-user key |
| `Settings::allForUser(int $userId, ?string $group): Collection` | Get all per-user settings |
| `Settings::flushForUser(int $userId)` | Remove all per-user settings |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## Support

If you find this project useful:

⭐ [Star the repo](https://github.com/philiprehberger/laravel-settings)

🐛 [Report issues](https://github.com/philiprehberger/laravel-settings/issues?q=is%3Aissue+is%3Aopen+label%3Abug)

💡 [Suggest features](https://github.com/philiprehberger/laravel-settings/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)

❤️ [Sponsor development](https://github.com/sponsors/philiprehberger)

🌐 [All Open Source Projects](https://philiprehberger.com/open-source-packages)

💻 [GitHub Profile](https://github.com/philiprehberger)

🔗 [LinkedIn Profile](https://www.linkedin.com/in/philiprehberger)

## License

[MIT](LICENSE)
