<?php

namespace PhilipRehberger\Settings;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

class SettingsCache
{
    private bool $enabled;

    private string $key;

    private ?int $ttl;

    public function __construct(
        private readonly CacheRepository $cache,
        bool $enabled,
        string $key,
        ?int $ttl,
    ) {
        $this->enabled = $enabled;
        $this->key = $key;
        $this->ttl = $ttl;
    }

    /**
     * Unique cache key scoped to a user (or global).
     */
    private function resolveKey(?int $userId): string
    {
        if ($userId !== null) {
            return $this->key.'_user_'.$userId;
        }

        return $this->key;
    }

    /**
     * Retrieve cached settings collection, or null on a miss.
     *
     * @return Collection<string, mixed>|null
     */
    public function get(?int $userId = null): ?Collection
    {
        if (! $this->enabled) {
            return null;
        }

        $cached = $this->cache->get($this->resolveKey($userId));

        return $cached instanceof Collection ? $cached : null;
    }

    /**
     * Store a settings collection in the cache.
     *
     * @param  Collection<string, mixed>  $settings
     */
    public function put(Collection $settings, ?int $userId = null): void
    {
        if (! $this->enabled) {
            return;
        }

        if ($this->ttl !== null) {
            $this->cache->put($this->resolveKey($userId), $settings, $this->ttl);
        } else {
            $this->cache->forever($this->resolveKey($userId), $settings);
        }
    }

    /**
     * Remove the cached collection for the given user scope.
     */
    public function invalidate(?int $userId = null): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->cache->forget($this->resolveKey($userId));
    }

    /**
     * Whether caching is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
