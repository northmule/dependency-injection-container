<?php

declare(strict_types=1);

namespace Northmule\Container;

use ArrayObject;


/**
 * Class ContainerFactory
 *
 * @package Northmule\Container
 */
class ContainerFactory
{
    use DependenciesTrait;
    
    /**
     * Create container
     *
     * @param array $config
     * @return Builder
     */
    public function __invoke(array $config): Builder
    {
        $this->containerId = 'service_container';
        $builder = new Builder();
        $this->configure($builder, $config);
        return $builder;
    }
    
    /**
     * Configure service
     *
     * @param Builder $builder
     * @param array            $config
     * @return void
     */
    private function configure(Builder $builder, array $config): void
    {
        $builder->set(ConfigKeys::CONFIG->value, new ArrayObject($config, ArrayObject::ARRAY_AS_PROPS));
        $this->configureDependencies($builder, $config[ConfigKeys::DEPENDENCIES->value]);
        $builder->compile();
    }

}
