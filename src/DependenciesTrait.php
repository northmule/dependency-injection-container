<?php

declare(strict_types=1);

namespace Northmule\Container;

use Northmule\Container\Exception\InvalidServiceException;
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
     * @param Builder $builder
     * @param array{"invokables":array, "factories": array, "aliases": array, "auto":array, "services":array, "lazy":string}            $dependencies
     * @return void
     */
    private function configureDependencies(Builder $builder, array $dependencies): void
    {
        foreach ($dependencies as $type => $services) {
            match ($type) {
                ConfigKeys::SERVICES->value => $this->buildService($builder, $services),
                ConfigKeys::INVOKABLES->value => $this->buildInvokables($builder, $services),
                ConfigKeys::FACTORIES->value => $this->buildFactories($builder, $services),
                ConfigKeys::ALIASES->value => $this->buildAliases($builder, $services),
                ConfigKeys::AUTO->value => $this->buildAuto($builder, $services),
                default => null,
            };
        }
        // Define lazy Services
        foreach ($dependencies[ConfigKeys::LAZY->value] ?? [] as $lazy) {
            if (!$builder->has($lazy)) {
                continue;
            }
            if (in_array($lazy, ($dependencies[ConfigKeys::FACTORIES->value] ?? []))) {
                continue;
            }
            $definition = $builder->getDefinition($lazy);
            $definition->setLazy(true);
        }
        // Define Non Shared Services
        foreach ($dependencies[ConfigKeys::NO_SHARE->value] ?? [] as $noShare) {
            if (!$builder->has($noShare)) {
                continue;
            }
            if (in_array($noShare, ($dependencies[ConfigKeys::FACTORIES->value] ?? []))) {
                continue;
            }
            $definition = $builder->getDefinition($noShare);
            $definition->setShared(false);
        }
    }
    
    /**
     * @param Builder $builder
     * @param array   $services
     *
     * @return void
     */
    private function buildService(Builder $builder, array $services): void
    {
        foreach ($services as $name => $service) {
            $builder->set($name, $service);
        }
    }
    
    /**
     * @param Builder $builder
     * @param array   $services
     *
     * @return void
     */
    private function buildInvokables(Builder $builder, array $services): void
    {
        foreach ($services as $name => $service) {
            if (is_numeric($name)) {
                $builder->register($service, $service)->setPublic(true);
                continue;
            }
            $builder->register($name, $service)->setPublic(true);
        }
    }
    
    /**
     * @param Builder $builder
     * @param array   $services
     *
     * @return void
     */
    private function buildFactories(Builder $builder, array $services): void
    {
        foreach ($services as $name => $service) {
            $class = is_string($service) ? $service : $name;
            $definition = $builder->register($name);
            $definition->setLazy(false);
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
    }
    
    /**
     * @param Builder $builder
     * @param array   $services
     *
     * @return void
     */
    private function buildAliases(Builder $builder, array $services): void
    {
        foreach ($services as $name => $service) {
            $builder->setAlias($name, $service)->setPublic(true);
        }
    }
    
    /**
     * @param Builder $builder
     * @param array   $services
     *
     * @return void
     */
    private function buildAuto(Builder $builder, array $services): void
    {
        foreach ($services as $service) {
            $builder->autowire($service);
            $builder->getDefinition($service)->setPublic(true);
        }
    }
}