<?php

declare(strict_types=1);

namespace Northmule\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarExporter\LazyProxyTrait;

use function strlen;
use function strpos;
use function get_class;

/**
 * Class Builder
 *
 * @package Northmule\Container
 */
final class Builder extends ContainerBuilder
{
    
    /**
     * Returns the original object of the requested class
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
        /** @var LazyProxyTrait $service */
        $service =  parent::get($id, $invalidBehavior);
        $serviceClass = get_class($service);
        // @see \Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\LazyServiceDumper::getProxyClass()
        if (strlen($serviceClass) > 12 &&
            (strpos($serviceClass, 'Proxy', -12) !== false
                || strpos($serviceClass, 'Ghost', -12) !== false)
        ) {
            return $service->initializeLazyObject();
        }
        return $service;
    }
    
    /**
     * Returns the proxy class containing the original object
     *
     * @param string $id
     *
     * @return LazyProxyTrait|object
     * @throws \Exception
     */
    public function getLazyObject(string $id): ?object
    {
        return parent::get($id);
    }
    
}