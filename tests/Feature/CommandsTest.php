<?php

namespace PhilipRehberger\Settings\Tests\Feature;

use PhilipRehberger\Settings\Facades\Settings;
use PhilipRehberger\Settings\Tests\TestCase;

class CommandsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // settings:list
    // -------------------------------------------------------------------------

    public function test_settings_list_shows_all_settings(): void
    {
        Settings::set('app.name', 'My App');
        Settings::set('app.debug', false);

        $this->artisan('settings:list')
            ->assertSuccessful()
            ->expectsTable(['Key', 'Value'], [
                ['app.name', 'My App'],
                ['app.debug', 'false'],
            ]);
    }

    public function test_settings_list_filters_by_group(): void
    {
        Settings::set('mail.host', 'localhost');
        Settings::set('app.name', 'My App');

        $this->artisan('settings:list', ['--group' => 'mail'])
            ->assertSuccessful()
            ->expectsTable(['Key', 'Value'], [
                ['mail.host', 'localhost'],
            ]);
    }

    public function test_settings_list_shows_info_when_empty(): void
    {
        $this->artisan('settings:list')
            ->assertSuccessful();
    }

    public function test_settings_list_shows_info_when_group_is_empty(): void
    {
        Settings::set('app.name', 'My App');

        $this->artisan('settings:list', ['--group' => 'nonexistent'])
            ->assertSuccessful();
    }

    // -------------------------------------------------------------------------
    // settings:get
    // -------------------------------------------------------------------------

    public function test_settings_get_shows_value(): void
    {
        Settings::set('app.name', 'My App');

        $this->artisan('settings:get', ['key' => 'app.name'])
            ->assertSuccessful()
            ->expectsTable(['Key', 'Value'], [
                ['app.name', 'My App'],
            ]);
    }

    public function test_settings_get_fails_for_missing_key(): void
    {
        $this->artisan('settings:get', ['key' => 'nonexistent'])
            ->assertFailed();
    }

    public function test_settings_get_formats_bool_as_string(): void
    {
        Settings::set('debug', true);

        $this->artisan('settings:get', ['key' => 'debug'])
            ->assertSuccessful()
            ->expectsTable(['Key', 'Value'], [
                ['debug', 'true'],
            ]);
    }

    public function test_settings_get_formats_array_as_json(): void
    {
        Settings::set('allowed.ips', ['127.0.0.1']);

        $this->artisan('settings:get', ['key' => 'allowed.ips'])
            ->assertSuccessful();
    }

    // -------------------------------------------------------------------------
    // settings:set
    // -------------------------------------------------------------------------

    public function test_settings_set_persists_string_value(): void
    {
        $this->artisan('settings:set', ['key' => 'app.name', 'value' => 'CLI App'])
            ->assertSuccessful();

        $this->assertSame('CLI App', Settings::get('app.name'));
    }

    public function test_settings_set_with_explicit_int_type(): void
    {
        $this->artisan('settings:set', [
            'key' => 'pagination.per_page',
            'value' => '50',
            '--type' => 'int',
        ])->assertSuccessful();

        $this->assertSame(50, Settings::get('pagination.per_page'));
    }

    public function test_settings_set_with_explicit_float_type(): void
    {
        $this->artisan('settings:set', [
            'key' => 'tax.rate',
            'value' => '0.19',
            '--type' => 'float',
        ])->assertSuccessful();

        $this->assertSame(0.19, Settings::get('tax.rate'));
    }

    public function test_settings_set_with_bool_true(): void
    {
        $this->artisan('settings:set', [
            'key' => 'feature.enabled',
            'value' => 'true',
            '--type' => 'bool',
        ])->assertSuccessful();

        $this->assertTrue(Settings::get('feature.enabled'));
    }

    public function test_settings_set_with_bool_false(): void
    {
        $this->artisan('settings:set', [
            'key' => 'feature.disabled',
            'value' => 'false',
            '--type' => 'bool',
        ])->assertSuccessful();

        $this->assertFalse(Settings::get('feature.disabled'));
    }

    public function test_settings_set_with_json_type(): void
    {
        $this->artisan('settings:set', [
            'key' => 'mail.config',
            'value' => '{"host":"smtp.example.com"}',
            '--type' => 'json',
        ])->assertSuccessful();

        $value = Settings::get('mail.config');
        $this->assertSame(['host' => 'smtp.example.com'], $value);
    }

    public function test_settings_set_fails_with_invalid_type(): void
    {
        $this->artisan('settings:set', [
            'key' => 'some.key',
            'value' => 'value',
            '--type' => 'invalid_type',
        ])->assertFailed();
    }

    public function test_settings_set_overwrites_existing_value(): void
    {
        Settings::set('app.name', 'Old Name');

        $this->artisan('settings:set', ['key' => 'app.name', 'value' => 'New Name'])
            ->assertSuccessful();

        $this->assertSame('New Name', Settings::get('app.name'));
    }
}
