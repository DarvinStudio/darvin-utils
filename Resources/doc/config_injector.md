Config injector
===============

## Описание

Config injector - [класс](../../DependencyInjection/ConfigInjector.php), осуществляющий инъекцию параметров конфигурации
 в DI-контейнер.

## Использование

Пример использования класса:

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

станет доступна в DI-контейнере в виде следующего набора параметров:

- "app.foo" = "foo";
- "app.bar" = array("baz" => "baz");
- "app.bar.baz" = "baz".
