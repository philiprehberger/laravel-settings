<?php

namespace PhilipRehberger\Settings;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;
use PhilipRehberger\Settings\Commands\SettingsGetCommand;
use PhilipRehberger\Settings\Commands\SettingsListCommand;
use PhilipRehberger\Settings\Commands\SettingsSetCommand;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/settings.php',
            'settings',
        );

        $this->app->singleton(SettingsRepository::class, function ($app) {
            return new SettingsRepository(
                $app->make(ConnectionInterface::class),
                (string) config('settings.table', 'settings'),
            );
        });

        $this->app->singleton(SettingsCache::class, function ($app) {
            /** @var array{enabled: bool, key: string, ttl: int|null} $cacheConfig */
            $cacheConfig = config('settings.cache');

            return new SettingsCache(
                $app->make(CacheRepository::class),
                (bool) ($cacheConfig['enabled'] ?? true),
                (string) ($cacheConfig['key'] ?? 'app_settings'),
                isset($cacheConfig['ttl']) ? (int) $cacheConfig['ttl'] : 3600,
            );
        });

        $this->app->singleton(Settings::class, function ($app) {
            return new Settings(
                $app->make(SettingsRepository::class),
                $app->make(SettingsCache::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/settings.php' => config_path('settings.php'),
            ], 'settings-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'settings-migrations');

            $this->commands([
                SettingsListCommand::class,
                SettingsGetCommand::class,
                SettingsSetCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
