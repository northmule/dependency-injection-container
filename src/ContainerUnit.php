<?php

declare(strict_types=1);

namespace Northmule\Container;

use Northmule\Container\Exception\InvalidServiceException;
use Northmule\Container\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

use function sprintf;
use function get_class;
use function is_object;
use function is_string;
use function class_exists;
use function method_exists;
use function count;
use function is_array;
use function is_callable;

/**
 * Class ContainerUnit
 *
 * @package Northmule\Container
 */
class ContainerUnit implements ContainerInterface, ContainerAwareInterface
{
    use DependenciesTrait;
    
    /** @var Builder */
    protected Builder $containerUnit;
    /** @var ContainerInterface */
    protected ContainerInterface $container;
    /** @var string */
    protected string $instanceOf;

    /**
     * ContainerUnit constructor.
     *
     * @param array{"dependencies":array} $config
     * @param ContainerInterface   $container
     * @param string               $instanceOf
     */
    public function __construct(
        array $config,
        ContainerInterface $container,
        string $instanceOf
    ) {
        $this->containerId = static::class;
        $this->container = $container;
        $this->instanceOf = $instanceOf;
        $this->containerUnit = new Builder();
        $this->configure($this->containerUnit, $config);
    }

    /**
     * {@inheritDoc}
     * @template T of object
     * @param class-string<T> $id
     * @return T
     * @throws \Exception
     * @phpstan-return T
     */
    public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException(sprintf('Can\'t create service with name %s', $id));
        }
        if ($id === 'service_container') {
            /** @phpstan-ignore-next-line  */
            return $this;
        }
        $service = $this->containerUnit->get($id);
        if (!is_object($service) || !$this->validate($service)) {
            throw new InvalidServiceException(
                sprintf('Service with name %s is invalid, expected %s interface', $id, $this->instanceOf)
            );
        }
        /** @phpstan-ignore-next-line  */
        return $service;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->containerUnit->has($id);
    }

    /**
     * Register service
     *
     * @param object $service
     * @return $this
     */
    public function addService(object $service): ContainerUnit
    {
        return $this->set(get_class($service), $service);
    }

    /**
     * Sets a service.
     *
     * @param string $id
     * @param object $service
     * @return $this
     */
    public function set(string $id, object $service): ContainerUnit
    {
        if (!$this->validate($service)) {
            throw new InvalidServiceException(
                sprintf('Expected %s interface, got %s', $this->instanceOf, get_class($service))
            );
        }
        $this->containerUnit->set($id, $service);
        return $this;
    }

    /**
     * Removes a service definition.
     *
     * @param string $id
     * @return $this
     */
    public function removeDefinition(string $id): ContainerUnit
    {
        $this->containerUnit->removeDefinition($id);
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Validate service
     *
     * @param object $service
     * @return bool
     */
    protected function validate(object $service): bool
    {
        return $service instanceof $this->instanceOf;
    }

    /**
     * Configure service
     *
     * @param Builder $builder
     * @param array{"dependencies":array} $config
     *
     * @return void
     */
    private function configure(Builder $builder, array $config): void
    {
        $builder->set(ConfigKeys::CONFIG->value, new \ArrayObject($config, \ArrayObject::ARRAY_AS_PROPS));
        $builder->set(static::class, $this);
        $builder->register(static::class, static::class)
            ->setLazy(true)
            ->setPublic(true);
        $this->configureDependencies($builder, $config[ConfigKeys::DEPENDENCIES->value]);
        $builder->compile();
    }
}
