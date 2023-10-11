<?php

declare(strict_types=1);

namespace Northmule\Container;

/**
 * Class Keys
 *
 * @package Northmule\Container
 */
enum Keys: string
{
    case INVOKABLES = 'invokables';
    case FACTORIES = 'factories';
    case ALIASES = 'aliases';
    case AUTO = 'auto';
    case SERVICES = 'services';
}
