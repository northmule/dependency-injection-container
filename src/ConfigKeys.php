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
}
