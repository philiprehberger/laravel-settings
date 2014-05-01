<?php

namespace PhilipRehberger\Settings\Tests\Feature;

use PhilipRehberger\Settings\Facades\Settings;
use PhilipRehberger\Settings\Tests\TestCase;

class SettingsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // get / set / has / forget
    // -------------------------------------------------------------------------

    public function test_set_and_get_string(): void
    {
        Settings::set('app.name', 'My Portal');

        $this->assertSame('My Portal', Settings::get('app.name'));
    }

    public function test_get_returns_null_for_unknown_key(): void
    {
        $this->assertNull(Settings::get('nonexistent'));
    }

    public function test_get_returns_default_argument_when_key_missing(): void
    {
        $this->assertSame('fallback', Settings::get('missing', 'fallback'));
    }

    public function test_get_returns_config_default_before_argument_default(): void
    {
        $this->app['config']->set('settings.defaults.app.timezone', 'Europe/Berlin');

        $this->assertSame('Europe/Berlin', Settings::get('app.timezone', 'UTC'));
    }

    public function test_has_returns_true_after_set(): void
    {
        Settings::set('flag', 'yes');

        $this->assertTrue(Settings::has('flag'));
    }

    public function test_has_returns_false_for_unknown_key(): void
    {
        $this->assertFalse(Settings::has('nonexistent'));
    }

    public function test_forget_removes_setting(): void
    {
        Settings::set('temp', 'value');
        Settings::forget('temp');

        $this->assertFalse(Settings::has('temp'));
        $this->assertNull(Settings::get('temp'));
    }

    public function test_flush_clears_all_settings(): void
    {
        Settings::set('a', '1');
        Settings::set('b', '2');
        Settings::flush();

        $this->assertTrue(Settings::all()->isEmpty());
    }

    // -------------------------------------------------------------------------
    // Type casting
    // -------------------------------------------------------------------------

    public function test_integer_type_casting(): void
    {
        Settings::set('pagination.per_page', 25);

        $value = Settings::get('pagination.per_page');

        $this->assertIsInt($value);
        $this->assertSame(25, $value);
    }

    public function test_float_type_casting(): void
    {
        Settings::set('tax.rate', 0.2);

        $value = Settings::get('tax.rate');

        $this->assertIsFloat($value);
        $this->assertSame(0.2, $value);
    }

    public function test_bool_true_type_casting(): void
    {
        Settings::set('feature.enabled', true);

        $value = Settings::get('feature.enabled');

        $this->assertIsBool($value);
        $this->assertTrue($value);
    }

    public function test_bool_false_type_casting(): void
    {
        Settings::set('feature.disabled', false);

        $value = Settings::get('feature.disabled');

        $this->assertIsBool($value);
        $this->assertFalse($value);
    }

    public function test_array_type_casting(): void
    {
        Settings::set('allowed.ips', ['127.0.0.1', '10.0.0.1']);

        $value = Settings::get('allowed.ips');

        $this->assertIsArray($value);
        $this->assertSame(['127.0.0.1', '10.0.0.1'], $value);
    }

    public function test_explicit_type_override(): void
    {
        // Pass a string but declare it as int
        Settings::set('items.count', '10', 'int');

        $value = Settings::get('items.count');

        $this->assertIsInt($value);
        $this->assertSame(10, $value);
    }

    public function test_json_type_stores_and_retrieves_object_shape(): void
    {
        $data = ['host' => 'smtp.example.com', 'port' => 587];

        Settings::set('mail.config', $data, 'json');

        $value = Settings::get('mail.config');

        $this->assertSame($data, $value);
    }

    // -------------------------------------------------------------------------
    // Group filtering
    // -------------------------------------------------------------------------

    public function test_all_returns_all_settings(): void
    {
        Settings::set('mail.host', 'localhost');
        Settings::set('mail.port', 25);
        Settings::set('app.name', 'Test');

        $this->assertCount(3, Settings::all());
    }

    public function test_all_filtered_by_group(): void
    {
        Settings::set('mail.host', 'localhost');
        Settings::set('mail.port', 25);
        Settings::set('app.name', 'Test');

        $mailSettings = Settings::all('mail');

        $this->assertCount(2, $mailSettings);
        $this->assertTrue($mailSettings->has('mail.host'));
        $this->assertTrue($mailSettings->has('mail.port'));
        $this->assertFalse($mailSettings->has('app.name'));
    }

    public function test_all_filtered_by_group_returns_empty_for_unknown_group(): void
    {
        Settings::set('app.name', 'Test');

        $this->assertTrue(Settings::all('nonexistent')->isEmpty());
    }

    // -------------------------------------------------------------------------
    // Cache invalidation
    // -------------------------------------------------------------------------

    public function test_cache_is_populated_after_first_get(): void
    {
        Settings::set('cached.key', 'cached-value');

        // First read warms the cache
        Settings::get('cached.key');

        // Second read must come from the cache (no exception means it works)
        $this->assertSame('cached-value', Settings::get('cached.key'));
    }

    public function test_cache_is_invalidated_after_set(): void
    {
        Settings::set('key', 'original');
        Settings::get('key');          // warm cache

        Settings::set('key', 'updated');

        $this->assertSame('updated', Settings::get('key'));
    }

    public function test_cache_is_invalidated_after_forget(): void
    {
        Settings::set('key', 'value');
        Settings::get('key');          // warm cache

        Settings::forget('key');

        $this->assertFalse(Settings::has('key'));
    }

    public function test_cache_is_invalidated_after_flush(): void
    {
        Settings::set('a', '1');
        Settings::set('b', '2');
        Settings::all();               // warm cache

        Settings::flush();

        $this->assertTrue(Settings::all()->isEmpty());
    }

    // -------------------------------------------------------------------------
    // Per-user settings
    // -------------------------------------------------------------------------

    public function test_per_user_set_and_get(): void
    {
        Settings::setForUser(1, 'theme', 'dark');

        $this->assertSame('dark', Settings::getForUser(1, 'theme'));
    }

    public function test_per_user_settings_are_isolated_from_global(): void
    {
        Settings::set('theme', 'system');
        Settings::setForUser(1, 'theme', 'dark');

        $this->assertSame('system', Settings::get('theme'));
        $this->assertSame('dark', Settings::getForUser(1, 'theme'));
    }

    public function test_per_user_settings_are_isolated_from_each_other(): void
    {
        Settings::setForUser(1, 'theme', 'dark');
        Settings::setForUser(2, 'theme', 'light');

        $this->assertSame('dark', Settings::getForUser(1, 'theme'));
        $this->assertSame('light', Settings::getForUser(2, 'theme'));
    }

    public function test_has_for_user(): void
    {
        Settings::setForUser(1, 'locale', 'de');

        $this->assertTrue(Settings::hasForUser(1, 'locale'));
        $this->assertFalse(Settings::hasForUser(2, 'locale'));
    }

    public function test_forget_for_user(): void
    {
        Settings::setForUser(1, 'locale', 'de');
        Settings::forgetForUser(1, 'locale');

        $this->assertFalse(Settings::hasForUser(1, 'locale'));
    }

    public function test_all_for_user_filtered_by_group(): void
    {
        Settings::setForUser(1, 'mail.host', 'smtp.example.com');
        Settings::setForUser(1, 'mail.port', 587);
        Settings::setForUser(1, 'app.locale', 'en');

        $mailSettings = Settings::allForUser(1, 'mail');

        $this->assertCount(2, $mailSettings);
        $this->assertFalse($mailSettings->has('app.locale'));
    }

    public function test_flush_for_user_does_not_affect_other_users(): void
    {
        Settings::setForUser(1, 'theme', 'dark');
        Settings::setForUser(2, 'theme', 'light');

        Settings::flushForUser(1);

        $this->assertFalse(Settings::hasForUser(1, 'theme'));
        $this->assertTrue(Settings::hasForUser(2, 'theme'));
    }

    public function test_per_user_get_falls_back_to_default_argument(): void
    {
        $this->assertSame('system', Settings::getForUser(1, 'theme', 'system'));
    }

    public function test_per_user_get_falls_back_to_config_default(): void
    {
        $this->app['config']->set('settings.defaults.theme', 'system');

        $this->assertSame('system', Settings::getForUser(1, 'theme', 'other'));
    }

    // -------------------------------------------------------------------------
    // Overwrite
    // -------------------------------------------------------------------------

    public function test_set_overwrites_existing_value(): void
    {
        Settings::set('app.name', 'First');
        Settings::set('app.name', 'Second');

        $this->assertSame('Second', Settings::get('app.name'));
        $this->assertCount(1, Settings::all());
    }
}
