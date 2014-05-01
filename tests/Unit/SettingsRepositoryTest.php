<?php

namespace PhilipRehberger\Settings\Tests\Unit;

use PhilipRehberger\Settings\SettingsRepository;
use PhilipRehberger\Settings\Tests\TestCase;

class SettingsRepositoryTest extends TestCase
{
    private SettingsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(SettingsRepository::class);
    }

    public function test_upsert_inserts_new_row(): void
    {
        $this->repository->upsert('app.name', 'My App', 'string', 'app');

        $row = $this->repository->find('app.name');

        $this->assertNotNull($row);
        $this->assertSame('My App', $row->value);
        $this->assertSame('string', $row->type);
        $this->assertSame('app', $row->group);
    }

    public function test_upsert_updates_existing_row(): void
    {
        $this->repository->upsert('app.name', 'First', 'string', 'app');
        $this->repository->upsert('app.name', 'Second', 'string', 'app');

        $this->assertSame(1, $this->repository->all()->count());

        $row = $this->repository->find('app.name');
        $this->assertSame('Second', $row->value);
    }

    public function test_find_returns_null_for_missing_key(): void
    {
        $this->assertNull($this->repository->find('nonexistent'));
    }

    public function test_delete_removes_row(): void
    {
        $this->repository->upsert('app.name', 'My App', 'string', 'app');
        $this->repository->delete('app.name');

        $this->assertNull($this->repository->find('app.name'));
    }

    public function test_flush_removes_all_global_rows(): void
    {
        $this->repository->upsert('key.one', 'a', 'string', 'key');
        $this->repository->upsert('key.two', 'b', 'string', 'key');

        $this->repository->flush();

        $this->assertSame(0, $this->repository->all()->count());
    }

    public function test_user_scoped_rows_are_isolated(): void
    {
        $this->repository->upsert('theme', 'dark', 'string', null, 1);
        $this->repository->upsert('theme', 'light', 'string', null, 2);

        $rowUser1 = $this->repository->find('theme', 1);
        $rowUser2 = $this->repository->find('theme', 2);

        $this->assertSame('dark', $rowUser1->value);
        $this->assertSame('light', $rowUser2->value);
    }

    public function test_flush_only_removes_user_scoped_rows(): void
    {
        $this->repository->upsert('theme', 'system', 'string', null);
        $this->repository->upsert('theme', 'dark', 'string', null, 1);

        $this->repository->flush(1);

        $this->assertSame(1, $this->repository->all()->count());
        $this->assertNull($this->repository->find('theme', 1));
    }

    public function test_group_from_key_extracts_prefix(): void
    {
        $this->assertSame('mail', $this->repository->groupFromKey('mail.from'));
        $this->assertSame('mail', $this->repository->groupFromKey('mail.from.name'));
        $this->assertNull($this->repository->groupFromKey('standalone'));
    }

    public function test_serialize_bool_true(): void
    {
        $result = $this->repository->serialize(true);

        $this->assertSame('1', $result['serialized']);
        $this->assertSame('bool', $result['type']);
    }

    public function test_serialize_bool_false(): void
    {
        $result = $this->repository->serialize(false);

        $this->assertSame('0', $result['serialized']);
        $this->assertSame('bool', $result['type']);
    }

    public function test_serialize_int(): void
    {
        $result = $this->repository->serialize(42);

        $this->assertSame('42', $result['serialized']);
        $this->assertSame('int', $result['type']);
    }

    public function test_serialize_float(): void
    {
        $result = $this->repository->serialize(3.14);

        $this->assertSame('3.14', $result['serialized']);
        $this->assertSame('float', $result['type']);
    }

    public function test_serialize_array(): void
    {
        $result = $this->repository->serialize(['a' => 1]);

        $this->assertSame('{"a":1}', $result['serialized']);
        $this->assertSame('array', $result['type']);
    }

    public function test_serialize_respects_explicit_type(): void
    {
        $result = $this->repository->serialize('42', 'int');

        $this->assertSame('int', $result['type']);
    }

    public function test_cast_value_int(): void
    {
        $this->assertSame(99, $this->repository->castValue('99', 'int'));
    }

    public function test_cast_value_float(): void
    {
        $this->assertSame(1.5, $this->repository->castValue('1.5', 'float'));
    }

    public function test_cast_value_bool_true(): void
    {
        $this->assertTrue($this->repository->castValue('1', 'bool'));
    }

    public function test_cast_value_bool_false(): void
    {
        $this->assertFalse($this->repository->castValue('0', 'bool'));
    }

    public function test_cast_value_array(): void
    {
        $this->assertSame(['x' => 2], $this->repository->castValue('{"x":2}', 'array'));
    }

    public function test_cast_value_string_passthrough(): void
    {
        $this->assertSame('hello', $this->repository->castValue('hello', 'string'));
    }
}
