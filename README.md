# dependency-injection-container
Symfony-based dependency container for Laminas projects

```shell
composer require northmule/dependency-injection-container
```

### Connection to the project. Transferring settings with dependencies to the constructor

**config/container.php**

```php 
declare(strict_types=1);

$repository = \Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
    ->addAdapter(\Dotenv\Repository\Adapter\PutenvAdapter::class)
    ->immutable()
    ->make();
\Dotenv\Dotenv::create($repository, \realpath(__DIR__) . '/../')->load();

$config = require realpath(__DIR__) . '/config.php';

$container = (new \Northmule\Container\ContainerFactory())($config); // Initializing a container for a project

return $container;
```
### Application configuration example
**config/config.php**
```php
<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$cacheConfig = ['config_cache_path' => 'data/cache/config.php'];
$aggregator = new ConfigAggregator([
    new ArrayProvider($cacheConfig),
    // Module Configurations
    Coderun\ModuleName\ConfigProvider::class,
    Coderun\ModuleName2\ConfigProvider::class,
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),
],$cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();


```

### Example of a factory and accessing a dependency container when configuring a class

**module/ModuleName/src/ModuleFolder/Factory/ExampleFactory.php**

```php

<?php

declare(strict_types=1);

namespace Coderun\ModuleName\ModuleFolder\Factory;

use Psr\Container\ContainerInterface;

/**
 * Class ExampleFactory
 */
class ExampleFactory
{
    /**
     * Create service Example
     *
     * @param ContainerInterface  $container
     * @param string              $requestedName
     * @param array<string,mixed> $options
     * @return Example
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = []
    ): UploadVideoHandler {
        /** @var ModuleOptions $config */
        $config = $container->get(ModuleOptions::class);
        /** @var ExampleService $service */
        $service = $container->get(ExampleService::class);
        return new Example($service);
    }
}


```

### Dependency settings for a specific module. When calling $container->get(ClassName), classes from getDependencies will be called (configured)

**module/ModuleName/src/ConfigProvider.php**

```php

<?php

declare(strict_types=1);

namespace Coderun\ModuleName;

/**
 * ConfigProvider
 */
class ConfigProvider
{
    /** @var string  */
    public const CONFIG_KEY = 'example_config';

    /**
     * @return array<string, array<array<string>>>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * @return array<string, array<string,string>>
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
            // Creating an instance without passing parameters to the constructor
            Example3::class,
            // or
            Example3::class => Example3::class,
            ],
            'auto' => [
               // The class will be configured based on the types specified in the class constructor
                \Coderun\ModuleFolder\Service\Example2::class,
            ],
            'factories'  => [
            // Setup based on a previously created factory
                Example::class  => ExampleFactory::class,
            ],
        ];
    }
}

```

### Example of running a script/project. In general, your application launch point

**script/app_run.php**

```php

<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

/** @var ContainerInterface  $container */
$container = require 'config/container.php';

/**
* Here is the logic of your application/script. From the container you can get all the configured objects specified in the files ConfigProvider.php
 */

```