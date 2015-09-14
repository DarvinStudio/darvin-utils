New entity counter
==================

## Описание

New entity counter - сервис для подсчета количества новых сущностей.

## Использование

__1. Помечаем свойство, являющееся флагом, показывающим, что сущность новая, аннотацией «\Darvin\Utils\Mapping\Annotation\NewObjectFlag».__

Пример:

```php
use Darvin\Utils\Mapping\Annotation as Darvin;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Page
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Darvin\NewObjectFlag
     */
    private $new;
}
```

__2. Используем метод «count()» сервиса «darvin_utils.new_object.counter.entity» для подсчета количества новых сущностей:__

```php
$newPagesCount = $this->getContainer()->get('darvin_utils.new_object.counter.entity')->count('AppBundle:Page');
```

Для проверки, возможен ли подсчет новых сущностей того или иного класса, сервис содержит метод «isCountable()».

Эти же методы доступны в Twig через функции «utils_count_new_objects()» и «utils_new_objects_countable()» соответственно.
