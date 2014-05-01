<?php

namespace PhilipRehberger\Settings\Commands;

use Illuminate\Console\Command;
use PhilipRehberger\Settings\Settings;

class SettingsListCommand extends Command
{
    protected $signature = 'settings:list {--group= : Filter by group (prefix before the first dot)}';

    protected $description = 'List all application settings stored in the database';

    public function handle(Settings $settings): int
    {
        $group = $this->option('group');

        /** @var string|null $group */
        $all = $settings->all(is_string($group) ? $group : null);

        if ($all->isEmpty()) {
            $this->components->info('No settings found'.($group ? " in group \"{$group}\"" : '').'.');

            return self::SUCCESS;
        }

        $rows = $all->map(function (mixed $value, string $key) {
            return [$key, $this->formatValue($value)];
        })->values()->all();

        $this->table(['Key', 'Value'], $rows);

        return self::SUCCESS;
    }

    private function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
