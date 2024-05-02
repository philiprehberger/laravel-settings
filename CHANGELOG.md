# Changelog

All notable changes to `philiprehberger/laravel-settings` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.2] - 2026-03-31

### Changed
- Standardize README to 3-badge format with emoji Support section
- Update CI checkout action to v5 for Node.js 24 compatibility
- Add GitHub issue templates, dependabot config, and PR template

## [1.1.1] - 2026-03-23

### Changed
- Remove non-standard Features section from README per template guide

## [1.1.0] - 2026-03-22

### Added
- `increment(string $key, int|float $amount = 1): int|float` — atomically increment a numeric setting
- `decrement(string $key, int|float $amount = 1): int|float` — atomically decrement a numeric setting
- `getMany(array $keys): array` — retrieve multiple settings at once
- `setMany(array $values): void` — store multiple settings at once

## [1.0.6] - 2026-03-21

### Changed
- Consolidate README and configuration updates from diverged branch

## [1.0.4] - 2026-03-18

### Fixed
- Add generic types to Facade `@method` PHPDoc for `all()` and `allForUser()` (`Collection<int, \stdClass>`)
- Fix `SettingsRepository::all()` return type from `Collection<int, object>` to `Collection<int, \stdClass>`
- Simplify `SettingsRepository::find()` — `first()` returns `object|null`, not `false`
- Remove stale PHPStan ignored error pattern for unreachable else branch

## [1.0.3] - 2026-03-18

### Fixed
- Widen phpstan-phpunit and extension-installer version constraints for PHPStan 2.x compatibility

## [1.0.2] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

## [1.0.1] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts

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

[Unreleased]: https://github.com/philiprehberger/laravel-settings/compare/v1.1.1...HEAD
[1.1.1]: https://github.com/philiprehberger/laravel-settings/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/philiprehberger/laravel-settings/compare/v1.0.6...v1.1.0
[1.0.6]: https://github.com/philiprehberger/laravel-settings/compare/v1.0.4...v1.0.6
[1.0.4]: https://github.com/philiprehberger/laravel-settings/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/philiprehberger/laravel-settings/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/philiprehberger/laravel-settings/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/philiprehberger/laravel-settings/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/philiprehberger/laravel-settings/releases/tag/v1.0.0
