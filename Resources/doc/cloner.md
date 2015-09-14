Cloner
======

## Описание

Cloner - сервис для клонирования сущностей.

## Использование

__1. Помечаем класс сущности как клонируемый, используя аннотацию «\Darvin\Utils\Mapping\Annotation\Clonable\Clonable».__

При этом доступны две стратегии копрования (аргумент «copyingPolicy» аннотации): «ALL» и «NONE» (по умолчанию). При использовании
первой будут копироваться значения всех свойств, за исключением помеченных аннотацией «\Darvin\Utils\Mapping\Annotation\Clonable\Skip»,
во втором случае будут копироваться значения только тех свойств, которые помечены «\Darvin\Utils\Mapping\Annotation\Clonable\Copy».

*(!) Идентификатор сущности не должен быть копируемым.*

*(!) Если значением свойства является сущность, она должна быть также помечена аннотацией «Clonable».*

Пример клонируемой сущности:

```php
use Darvin\Utils\Mapping\Annotation\Clonable as Clonable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Clonable\Clonable(copyingPolicy="ALL")
 */
class Page
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Id
     * @Clonable\Skip
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;
}
```

__2. Используем метод «createClone()» сервиса «darvin_utils.cloner»:__

```php
$page = new Page();
$page->setContent('Hello, cloner!');

$pageClone = $this->getContainer()->get('darvin_utils.cloner')->createClone($page);
```
