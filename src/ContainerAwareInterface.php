<?php

declare(strict_types=1);

namespace Northmule\Container;

use Psr\Container\ContainerInterface;

/**
 * Interface ContainerAwareInterface
 *
 * @package Northmule\Container
 */
interface ContainerAwareInterface
{
    /**
     * Return container
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;
}
