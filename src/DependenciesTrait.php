<?php

declare(strict_types=1);

namespace Northmule\Container;

use Northmule\Container\Exception\InvalidServiceException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function is_numeric;
use function is_string;
use function class_exists;
use function method_exists;
use function count;
use function is_array;
use function is_callable;

trait DependenciesTrait
{
    
    /**
     * @var string
     */
    private string $containerId = '';
    /**
     * Configure service
     *
     * @param ContainerBuilder $builder
     * @param array{"invokables":array, "factories": array, "aliases": array, "auto":array, "services":array}            $dependencies
     * @return void
     */
    private function configureDependencies(ContainerBuilder $builder, array $dependencies): void
    {
        foreach ($dependencies as $type => $services) {
            if ($type === ConfigKeys::SERVICES->value) {
                foreach ($services as $name => $service) {
                    $builder->set($name, $service);
                }
                continue;
            }
            if ($type === ConfigKeys::INVOKABLES->value) {
                foreach ($services as $name => $service) {
                    if (is_numeric($name)) {
                        $builder->register($service, $service)->setLazy(true)->setPublic(true);
                        continue;
                    }
                    $builder->register($name, $service)->setLazy(true)->setPublic(true);
                }
                continue;
            }
            if ($type === ConfigKeys::FACTORIES->value) {
                foreach ($services as $name => $service) {
                    $class = is_string($service) ? $service : $name;
                    $definition = $builder->register($name, $class);
                    $definition->setLazy(true);
                    $definition->setPublic(true);
                    $definition->setArguments([new Reference($this->containerId), $name]);
                    if (
                        is_string($service) && class_exists($service)
                        && method_exists($service, '__invoke')
                    ) {
                        $definition->setFactory(new Reference($service));
                        $builder->set($service, new $service());
                        continue;
                    }
                    if (is_array($service) && count($service) == 2 && is_callable($service)) {
                        if (class_exists($service[0])) {
                            $definition->setFactory($service);
                            continue;
                        }
                    }
                    if (!is_string($service)) {
                        throw InvalidServiceException::unsupportedType($class, $service);
                    }
                }
                continue;
            }
            if ($type === ConfigKeys::ALIASES->value) {
                foreach ($services as $name => $service) {
                    $builder->setAlias($name, $service)->setPublic(true);
                }
                continue;
            }
            if ($type === ConfigKeys::AUTO->value) {
                foreach ($services as $service) {
                    $builder->autowire($service, $service)->setLazy(true)->setPublic(true);
                }
            }
        }
    }
}