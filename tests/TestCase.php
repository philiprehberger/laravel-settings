<?php

namespace PhilipRehberger\Settings\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PhilipRehberger\Settings\SettingsServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SettingsServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Settings' => \PhilipRehberger\Settings\Facades\Settings::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.default', 'array');

        $app['config']->set('settings.cache.enabled', true);
        $app['config']->set('settings.cache.ttl', 3600);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
