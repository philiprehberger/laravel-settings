<?php

namespace PhilipRehberger\Settings\Commands;

use Illuminate\Console\Command;
use PhilipRehberger\Settings\Settings;

class SettingsGetCommand extends Command
{
    protected $signature = 'settings:get {key : The setting key to retrieve}';

    protected $description = 'Get the value of a single application setting';

    public function handle(Settings $settings): int
    {
        /** @var string $key */
        $key = $this->argument('key');

        if (! $settings->has($key)) {
            $this->components->error("Setting \"{$key}\" does not exist.");

            return self::FAILURE;
        }

        $value = $settings->get($key);

        $this->table(['Key', 'Value'], [
            [$key, $this->formatValue($value)],
        ]);

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
