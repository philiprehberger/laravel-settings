<?php

namespace PhilipRehberger\Settings\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void set(string $key, mixed $value, ?string $type = null)
 * @method static bool has(string $key)
 * @method static void forget(string $key)
 * @method static Collection<int, \stdClass> all(?string $group = null)
 * @method static void flush()
 * @method static array<string, mixed> getMany(array<int, string> $keys)
 * @method static void setMany(array<string, mixed> $values)
 * @method static int|float increment(string $key, int|float $amount = 1)
 * @method static int|float decrement(string $key, int|float $amount = 1)
 * @method static mixed getForUser(int $userId, string $key, mixed $default = null)
 * @method static void setForUser(int $userId, string $key, mixed $value, ?string $type = null)
 * @method static bool hasForUser(int $userId, string $key)
 * @method static void forgetForUser(int $userId, string $key)
 * @method static Collection<int, \stdClass> allForUser(int $userId, ?string $group = null)
 * @method static void flushForUser(int $userId)
 *
 * @see \PhilipRehberger\Settings\Settings
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PhilipRehberger\Settings\Settings::class;
    }
}
