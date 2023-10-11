<?php

declare(strict_types=1);

namespace Northmule\Container\Exception;

use RuntimeException;

use function sprintf;
use function gettype;

/**
 * Class InvalidServiceException
 *
 * @package Northmule\Container\Exception
 */
class InvalidServiceException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param string $name
     *
     * @return InvalidServiceException
     */
    public static function invalidParameter(string $name): InvalidServiceException
    {
        return new self(sprintf('Invalid configuration parameter: %s', $name));
    }
    
    /**
     * @param string                 $class
     * @param object|string|int|null $service
     *
     * @return InvalidServiceException
     */
    public static function unsupportedType(string $class, mixed $service): InvalidServiceException
    {
        return new self(sprintf('Unsupported type \'%s\' for %s', gettype($service), $class));
    }
}
