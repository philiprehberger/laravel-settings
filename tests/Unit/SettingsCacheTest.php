<?php

namespace PhilipRehberger\Settings\Tests\Unit;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use PhilipRehberger\Settings\SettingsCache;
use PhilipRehberger\Settings\Tests\TestCase;

class SettingsCacheTest extends TestCase
{
    private SettingsCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new SettingsCache(
            $this->app->make(CacheRepository::class),
            enabled: true,
            key: 'test_settings',
            ttl: 3600,
        );
    }

    public function test_get_returns_null_on_miss(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function test_put_and_get_roundtrip(): void
    {
        $collection = collect(['foo' => 'bar']);

        $this->cache->put($collection);

        $result = $this->cache->get();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame('bar', $result->get('foo'));
    }

    public function test_invalidate_clears_cache(): void
    {
        $this->cache->put(collect(['foo' => 'bar']));
        $this->cache->invalidate();

        $this->assertNull($this->cache->get());
    }

    public function test_user_scoped_cache_is_isolated(): void
    {
        $this->cache->put(collect(['theme' => 'dark']), 1);
        $this->cache->put(collect(['theme' => 'light']), 2);

        $this->assertSame('dark', $this->cache->get(1)->get('theme'));
        $this->assertSame('light', $this->cache->get(2)->get('theme'));
    }

    public function test_invalidate_user_scope_does_not_affect_global(): void
    {
        $this->cache->put(collect(['theme' => 'system']));
        $this->cache->put(collect(['theme' => 'dark']), 1);

        $this->cache->invalidate(1);

        $this->assertNull($this->cache->get(1));
        $this->assertNotNull($this->cache->get());
    }

    public function test_disabled_cache_always_returns_null(): void
    {
        $disabledCache = new SettingsCache(
            $this->app->make(CacheRepository::class),
            enabled: false,
            key: 'test_settings_disabled',
            ttl: 3600,
        );

        $disabledCache->put(collect(['x' => 1]));

        $this->assertNull($disabledCache->get());
    }

    public function test_is_enabled_reflects_constructor_argument(): void
    {
        $this->assertTrue($this->cache->isEnabled());

        $disabled = new SettingsCache(
            $this->app->make(CacheRepository::class),
            enabled: false,
            key: 'noop',
            ttl: 60,
        );

        $this->assertFalse($disabled->isEnabled());
    }
}
