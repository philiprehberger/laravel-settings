<?php

namespace PhilipRehberger\Settings;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class SettingsRepository
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly string $table,
    ) {}

    /**
     * Fetch all rows, optionally filtered to a single user, as a raw collection.
     *
     * @return Collection<int, object>
     */
    public function all(?int $userId = null): Collection
    {
        $query = $this->db->table($this->table);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id');
        }

        return $query->get();
    }

    /**
     * Find a single row by key (and optional user).
     */
    public function find(string $key, ?int $userId = null): ?object
    {
        $query = $this->db->table($this->table)->where('key', $key);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id');
        }

        $row = $query->first();

        return $row !== false ? $row : null;
    }

    /**
     * Insert or update a setting row.
     */
    public function upsert(string $key, string $serialized, string $type, ?string $group, ?int $userId = null): void
    {
        $existing = $this->find($key, $userId);

        $data = [
            'value' => $serialized,
            'type' => $type,
            'group' => $group,
            'updated_at' => now(),
        ];

        if ($existing) {
            $this->db->table($this->table)
                ->where('id', $existing->id)
                ->update($data);
        } else {
            $this->db->table($this->table)->insert(array_merge($data, [
                'key' => $key,
                'user_id' => $userId,
                'created_at' => now(),
            ]));
        }
    }

    /**
     * Delete a setting by key.
     */
    public function delete(string $key, ?int $userId = null): void
    {
        $query = $this->db->table($this->table)->where('key', $key);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id');
        }

        $query->delete();
    }

    /**
     * Delete all settings (optionally scoped to a user).
     */
    public function flush(?int $userId = null): void
    {
        $query = $this->db->table($this->table);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id');
        }

        $query->delete();
    }

    /**
     * Cast a raw value string back to its declared PHP type.
     */
    public function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => $value === '1' || $value === 'true',
            'array',
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Serialize a PHP value to a string for storage and determine its type.
     *
     * @return array{serialized: string, type: string}
     */
    public function serialize(mixed $value, ?string $type = null): array
    {
        if ($type === null) {
            $type = $this->detectType($value);
        }

        $serialized = match ($type) {
            'bool' => $value ? '1' : '0',
            'array',
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };

        return ['serialized' => $serialized, 'type' => $type];
    }

    /**
     * Detect the most specific type name for a given PHP value.
     */
    private function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'bool',
            is_int($value) => 'int',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }

    /**
     * Derive the group name from a dotted key (everything before the first dot).
     */
    public function groupFromKey(string $key): ?string
    {
        $pos = strpos($key, '.');

        return $pos !== false ? substr($key, 0, $pos) : null;
    }
}
