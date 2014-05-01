<?php

namespace PhilipRehberger\Settings;

use Illuminate\Support\Collection;

class Settings
{
    public function __construct(
        private readonly SettingsRepository $repository,
        private readonly SettingsCache $cache,
    ) {}

    // -------------------------------------------------------------------------
    // Core API
    // -------------------------------------------------------------------------

    /**
     * Retrieve a setting value.
     *
     * Resolution order:
     *   1. Database (via cache)
     *   2. config('settings.defaults.<key>')
     *   3. $default argument
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->loadAll();

        if ($all->has($key)) {
            return $all->get($key);
        }

        $configDefault = config('settings.defaults.'.$key);

        return $configDefault ?? $default;
    }

    /**
     * Persist a setting value.
     *
     * @param  string|null  $type  Explicit type override: string|int|float|bool|array|json
     */
    public function set(string $key, mixed $value, ?string $type = null): void
    {
        ['serialized' => $serialized, 'type' => $resolvedType] = $this->repository->serialize($value, $type);

        $group = $this->repository->groupFromKey($key);

        $this->repository->upsert($key, $serialized, $resolvedType, $group);

        $this->cache->invalidate();
    }

    /**
     * Determine whether a key exists in the database.
     */
    public function has(string $key): bool
    {
        return $this->loadAll()->has($key);
    }

    /**
     * Remove a setting from the database.
     */
    public function forget(string $key): void
    {
        $this->repository->delete($key);
        $this->cache->invalidate();
    }

    /**
     * Return all settings, optionally filtered to a group.
     *
     * The group is determined by the prefix before the first dot in the key.
     * E.g. key "mail.from" belongs to group "mail".
     *
     * @return Collection<string, mixed>
     */
    public function all(?string $group = null): Collection
    {
        $all = $this->loadAll();

        if ($group === null) {
            return $all;
        }

        return $all->filter(
            fn (mixed $value, string $key) => $this->repository->groupFromKey($key) === $group,
        );
    }

    /**
     * Delete every setting from the database and clear the cache.
     */
    public function flush(): void
    {
        $this->repository->flush();
        $this->cache->invalidate();
    }

    // -------------------------------------------------------------------------
    // Per-user API
    // -------------------------------------------------------------------------

    /**
     * Retrieve a per-user setting value.
     */
    public function getForUser(int $userId, string $key, mixed $default = null): mixed
    {
        $all = $this->loadAllForUser($userId);

        if ($all->has($key)) {
            return $all->get($key);
        }

        $configDefault = config('settings.defaults.'.$key);

        return $configDefault ?? $default;
    }

    /**
     * Persist a per-user setting value.
     */
    public function setForUser(int $userId, string $key, mixed $value, ?string $type = null): void
    {
        ['serialized' => $serialized, 'type' => $resolvedType] = $this->repository->serialize($value, $type);

        $group = $this->repository->groupFromKey($key);

        $this->repository->upsert($key, $serialized, $resolvedType, $group, $userId);

        $this->cache->invalidate($userId);
    }

    /**
     * Determine whether a per-user key exists.
     */
    public function hasForUser(int $userId, string $key): bool
    {
        return $this->loadAllForUser($userId)->has($key);
    }

    /**
     * Remove a per-user setting.
     */
    public function forgetForUser(int $userId, string $key): void
    {
        $this->repository->delete($key, $userId);
        $this->cache->invalidate($userId);
    }

    /**
     * Return all per-user settings, optionally filtered to a group.
     *
     * @return Collection<string, mixed>
     */
    public function allForUser(int $userId, ?string $group = null): Collection
    {
        $all = $this->loadAllForUser($userId);

        if ($group === null) {
            return $all;
        }

        return $all->filter(
            fn (mixed $value, string $key) => $this->repository->groupFromKey($key) === $group,
        );
    }

    /**
     * Delete all per-user settings.
     */
    public function flushForUser(int $userId): void
    {
        $this->repository->flush($userId);
        $this->cache->invalidate($userId);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Load the full settings map from cache or database (global scope).
     *
     * @return Collection<string, mixed>
     */
    private function loadAll(?int $userId = null): Collection
    {
        $cached = $this->cache->get($userId);

        if ($cached !== null) {
            return $cached;
        }

        $rows = $this->repository->all($userId);

        $map = $rows->mapWithKeys(function (object $row) {
            return [$row->key => $this->repository->castValue((string) $row->value, $row->type)];
        });

        $this->cache->put($map, $userId);

        return $map;
    }

    /**
     * Load the full settings map for a specific user.
     *
     * @return Collection<string, mixed>
     */
    private function loadAllForUser(int $userId): Collection
    {
        return $this->loadAll($userId);
    }
}
