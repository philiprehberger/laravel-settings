<?php

namespace PhilipRehberger\Settings\Commands;

use Illuminate\Console\Command;
use PhilipRehberger\Settings\Settings;

class SettingsSetCommand extends Command
{
    protected $signature = 'settings:set
                            {key   : The setting key}
                            {value : The value to store}
                            {--type= : Explicit type (string|int|float|bool|array|json)}';

    protected $description = 'Set an application setting value';

    private const VALID_TYPES = ['string', 'int', 'float', 'bool', 'array', 'json'];

    public function handle(Settings $settings): int
    {
        /** @var string $key */
        $key = $this->argument('key');

        /** @var string $rawValue */
        $rawValue = $this->argument('value');

        /** @var string|null $type */
        $type = $this->option('type');

        if ($type !== null && ! in_array($type, self::VALID_TYPES, true)) {
            $this->components->error(
                'Invalid type "'.$type.'". Must be one of: '.implode(', ', self::VALID_TYPES),
            );

            return self::FAILURE;
        }

        $value = $this->coerce($rawValue, $type);

        $settings->set($key, $value, $type);

        $this->components->info("Setting \"{$key}\" saved successfully.");

        return self::SUCCESS;
    }

    /**
     * Coerce the raw string argument to the target type when an explicit type is given.
     */
    private function coerce(string $rawValue, ?string $type): mixed
    {
        return match ($type) {
            'int' => (int) $rawValue,
            'float' => (float) $rawValue,
            'bool' => in_array(strtolower($rawValue), ['1', 'true', 'yes', 'on'], true),
            'array',
            'json' => json_decode($rawValue, true),
            default => $rawValue,
        };
    }
}
