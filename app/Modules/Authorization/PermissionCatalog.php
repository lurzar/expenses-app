<?php

namespace App\Modules\Authorization;

use BackedEnum;
use InvalidArgumentException;

final class PermissionCatalog
{
    /** @var array<string, PermissionDefinition&BackedEnum> */
    private array $definitions = [];

    /**
     * @param  class-string<PermissionDefinition&BackedEnum>  $enum
     */
    public function register(string $enum): void
    {
        foreach ($enum::cases() as $definition) {
            if (! is_string($definition->value) || preg_match('/^[a-z][a-z0-9-]*\.[a-z][a-z0-9-]*$/', $definition->value) !== 1) {
                throw new InvalidArgumentException('Permission names must use lowercase module.action format.');
            }

            if (isset($this->definitions[$definition->value])) {
                throw new InvalidArgumentException("Permission [{$definition->value}] is already registered.");
            }

            $this->definitions[$definition->value] = $definition;
        }

        ksort($this->definitions);
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_keys($this->definitions);
    }

    /** @return list<PermissionDefinition&BackedEnum> */
    public function definitions(): array
    {
        return array_values($this->definitions);
    }
}
