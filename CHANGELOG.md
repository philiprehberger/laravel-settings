# Changelog

All notable changes to `philiprehberger/laravel-settings` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-03-09

### Added
- `Settings::get()` with three-tier fallback (database → config defaults → argument default)
- `Settings::set()` with automatic type detection and optional explicit type override
- `Settings::has()`, `Settings::forget()`, `Settings::all()`, `Settings::flush()`
- Per-user settings via `getForUser()`, `setForUser()`, `hasForUser()`, `forgetForUser()`, `allForUser()`, `flushForUser()`
- Group filtering: `Settings::all('mail')` returns only keys prefixed with `mail.`
- Type-safe casting for `string`, `int`, `float`, `bool`, `array`, and `json`
- Single-key cache strategy with automatic invalidation on every write
- Configurable cache TTL and opt-out via `settings.cache.enabled`
- `settings:list {--group=}` Artisan command
- `settings:get {key}` Artisan command
- `settings:set {key} {value} {--type=}` Artisan command
- `Settings` Facade with full `@method` docblocks
- Auto-discovery via `extra.laravel` in `composer.json`
- Migration published via `php artisan vendor:publish --tag=settings-migrations`
- Config published via `php artisan vendor:publish --tag=settings-config`
- PHPStan level 8 compliance
- Laravel Pint code style enforcement
- GitHub Actions CI matrix: PHP 8.2 / 8.3 / 8.4 × Laravel 11 / 12

[Unreleased]: https://github.com/philiprehberger/laravel-settings/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/philiprehberger/laravel-settings/releases/tag/v1.0.0
