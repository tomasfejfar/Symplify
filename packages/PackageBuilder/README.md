# Package Builder

[![Build Status](https://img.shields.io/travis/Symplify/PackageBuilder/master.svg?style=flat-square)](https://travis-ci.org/Symplify/PackageBuilder)
[![Downloads](https://img.shields.io/packagist/dt/symplify/package-builder.svg?style=flat-square)](https://packagist.org/packages/symplify/package-builder)
[![Subscribe](https://img.shields.io/badge/subscribe-to--releases-green.svg?style=flat-square)](https://libraries.io/packagist/symplify%2Fpackage-builder)

This tools helps you with Collectors in DependecyInjection, Console shortcuts, ParameterProvider as service and many more.

## Install

```bash
composer require symplify/package-builder
```

## Usage

### 1.Usage in Symfony CompilerPass

#### Collect Services of Certain Type Together

```php
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symplify\PackageBuilder\DependencyInjection\DefinitionFinder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;

final class CollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            EventDispatcher::class,
            EventSubscriberInterface::class,
            'addSubscriber'
        );
    }
}
```

#### Add Service if Found

```php
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symplify\PackageBuilder\DependencyInjection\DefinitionFinder;

final class CustomSourceProviderDefinitionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $customSourceProviderDefinition = DefinitionFinder::getByTypeIfExists(
            $containerBuilder,
            CustomSourceProviderInterface::class
        );

        if ($customSourceProviderDefinition === null) {
            return;
        }

        $sourceFinderDefinition = DefinitionFinder::getByType($containerBuilder, SourceFinder::class);
        $sourceFinderDefinition->addMethodCall(
            'setCustomSourceProvider',
            [new Reference($customSourceProviderDefinition->getClass())]
        );
    }
}
```

### 2. All Parameters Available in a Service

Note: System parameters are excluded by default.

Register:

```yml
# app/config/services.yml

parameters:
    source: src

services:
    _defaults:
        autowire: true

    Symplify\PackageBuilder\Parameter\ParameterProvider: ~
```

Then require in `__construct()` where needed:

```php
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class StatieConfiguration
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function getSource(): string
    {
        return $parameterProvider->provide()['source']; // returns "src"
    }
}
```

### 3. Do you need a Vendor Directory?

```php
Symplify\PackageBuilder\Composer\VendorDirProvider::provide(); // return path to vendor directory
```

### 4. Load a Config for CLI Application?

Use in CLI entry file `bin/<app-name>`, e.g. `bin/statie` or `bin/apigen`.

```php
# bin/statie

use Symfony\Component\Console\Input\ArgvInput;

Symplify\PackageBuilder\Configuration\ConfigFilePathHelper::detectFromInput('statie', new ArgvInput);
# throws "Symplify\PackageBuilder\Exception\Configuration\FileNotFoundException"
# exception if no file is found
```

Where "statie" is key to save the location under. Later you'll use it get the config.

With `--config` you can set config via CLI.

```bash
bin/statie --config config/statie.yml
```

Then get the config just run:

```php
$config = Symplify\PackageBuilder\Configuration\ConfigFilePathHelper::provide('statie');
dump($config); // returns absolute path to "config/statie.yml"
// or NULL if none was found before
```

You can also provide fallback to file in [current working directory](http://php.net/manual/en/function.getcwd.php):

```php
$config = Symplify\PackageBuilder\Configuration\ConfigFilePathHelper::provide('statie', 'statie.yml');
```

This is common practise in CLI applications, e.g. [PHPUnit](https://phpunit.de/) looks for `phpunit.xml`.

### 5. Use SymfonyStyle for Console Output Anywhere You Need

Another use case for `bin/<app-name>`, when you need to output before building Dependency Injection Container. E.g. when ContainerFactory fails on exception that you need to report nicely.

```php
# bin/statie

$symfonyStyle = Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::create();
try {
    $containerFactory->create();
} catch (Throwable $throwable) {
    $symfonyStyle->error($throwable->getMessage());
}
```

### 6. Load `*.neon` config files in Kernel

You can load `*.yaml` files in Kernel by default. Now `*.neon` as well:

```php
namespace Symplify\PackageBuilder\Neon\NeonLoaderAwareKernelTrait;

final class SuperKernel extends Kernel
{
    use NeonLoaderAwareKernelTrait;

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/services.neon');
    }
}
````

### 7. Load config via `--level` option in your Console Application

In you `bin/your-app` you can use `--level` option as shortcut to load config from `/config` directory.

It makes is easier to load config over traditional super long way:

```bash
vendor/bin/your-app --config vendor/organization-name/package-name/config/subdirectory/the-config.yml
```

```php
use Symplify\PackageBuilder\Configuration\ConfigFilePathHelper;
use Symplify\PackageBuilder\Configuration\LevelConfigShortcutFinder;

// 1. Try --level
$configFile = (new LevelConfigShortcutFinder)->resolveLevel(new ArgvInput, __DIR__ . '/../config/');

// 2. try --config
if ($configFile === null) {
    ConfigFilePathHelper::detectFromInput('ecs', new ArgvInput);
    $configFile = ConfigFilePathHelper::provide('ecs', 'easy-coding-standard.neon');
}

// 3. Build DI container
$containerFactory = new ContainerFactory; // your own class
if ($configFile) {
    $container = $containerFactory->createWithConfig($configFile);
} else {
    $container = $containerFactory->create();
}
```

And use like:

```bash
vendor/bin/your-app --level the-config
```

### 8. Find `vendor/autoload.php` in specific directory for BetterReflection

When you use [BetterReflection](https://github.com/Roave/BetterReflection/) and [`ComposerSourceLocator`](https://github.com/Roave/BetterReflection/blob/master/UPGRADE.md#source-locators-now-require-additional-dependencies), you need to locate non-locator `/vendor/autoload.php`.

```php
$autolaodFile = Symplify\PackageBuilder\Composer\AutoloadFinder::findNearDirectories([
    __DIR__ . '/src'
]);

var_dump($autolaodFile); # contains: __DIR__ . '/vendor`
````

That's all :)
