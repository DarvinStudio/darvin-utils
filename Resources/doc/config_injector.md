Config injector
===============

## Описание

Config injector - [класс](../../DependencyInjection/ConfigInjector.php) для инжекта конфигурации в DI-контейнер в виде
набора параметров.

## Использование

Пример:

```php
namespace AppBundle\DependencyInjection;

use Darvin\Utils\DependencyInjection\ConfigInjector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $configInjector = new ConfigInjector();
        $configInjector->inject($config, $container, 'app');
    }
}
```

Конфигурация вида

```php
$rootNode
    ->children()
        ->scalarNode('foo')->defaultValue('foo')->end()
        ->arrayNode('bar')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('baz')->defaultValue('baz')->end()
            ->end()
        ->end()
    ->end();
```

попадет в DI-контейнер в виде трех параметров:

- "app.foo" = "foo"
- "app.bar" = [ "baz": "baz" ]
- "app.bar.baz" = "baz"
