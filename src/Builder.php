<?php

declare(strict_types=1);

namespace Northmule\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarExporter\LazyProxyTrait;

use function class_implements;
use function is_array;

/**
 * Class Builder
 *
 * @package Northmule\Container
 */
class Builder extends ContainerBuilder
{
    
    /**
     * Returns the proxy class containing the original object
     *
     * @template T
     * @param class-string<T> $id
     * @param int          $invalidBehavior
     *
     * @return T
     * @throws \Exception
     */
    public function get(string $id, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        return parent::get($id);
       
    }
    
    /**
     *
     * Returns the original object of the requested class
     * @template T
     * @param class-string<T> $id
     *
     * @return T
     * @throws \Exception
     */
    public function getOriginObject(string $id, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        /** @var LazyProxyTrait $service */
        $service =  parent::get($id, $invalidBehavior);
        $implementClass = class_implements($service);
        if (is_array($implementClass)
            && array_key_exists('Symfony\Component\VarExporter\LazyObjectInterface', $implementClass)) {
            return $service->initializeLazyObject();
        }
        return $service;
    }
}