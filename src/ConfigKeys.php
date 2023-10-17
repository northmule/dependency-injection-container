<?php

declare(strict_types=1);

namespace Northmule\Container;

/**
 * Enum ConfigKeys
 *
 * @package Northmule\Container
 */
enum ConfigKeys: string
{
    case INVOKABLES = 'invokables';
    case FACTORIES = 'factories';
    case ALIASES = 'aliases';
    case AUTO = 'auto';
    case SERVICES = 'services';
    case CONFIG = 'config';
    case DEPENDENCIES = 'dependencies';
    case LAZY = 'lazy';
    case NO_SHARE = 'no_share';
    
    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::INVOKABLES->value,
            self::FACTORIES->value,
            self::ALIASES->value,
            self::AUTO->value,
            self::SERVICES->value,
            self::LAZY->value,
            self::NO_SHARE->value,
        ];
    }
}
