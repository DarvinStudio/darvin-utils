Tagged service IDs sorter
=========================

## Описание

Tagged service IDs sorter - [класс](../../DependencyInjection/TaggedServiceIdsSorter.php) для сортировки тегированных сервисов.

## Использование

Пример:

```php
use Darvin\Utils\DependencyInjection\TaggedServiceIdsSorter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MenuPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $menuItems = $container->findTaggedServiceIds('darvin_admin.menu_item');

        $sorter = new TaggedServiceIdsSorter();
        $sorter->sort($menuItems);
    }
}
```

По умолчанию аргумент тега, содержащий позицию сервиса, должен называться «position».
