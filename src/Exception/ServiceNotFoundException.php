<?php

declare(strict_types=1);

namespace Northmule\Container\Exception;

use OutOfBoundsException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ServiceNotFoundException
 *
 * @package Northmule\Container\Exception
 */
class ServiceNotFoundException extends OutOfBoundsException implements
    ExceptionInterface,
    NotFoundExceptionInterface
{
}
