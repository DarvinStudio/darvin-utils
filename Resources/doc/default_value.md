Default value
=============

## Описание

Default value - функционал, который позволяет использовать значение одного свойства сущности в качестве значения по
умолчанию другого свойства.

## Использование

__1. Помечаем необходимые свойства аннотацией «\Darvin\Utils\Mapping\Annotation\DefaultValue».__

Эта аннотация имеет один аргумент - «sourcePropertyPath» - путь до свойства, из которого нужно брать значение по умолчанию.

Пример:

```php
use Darvin\Utils\Mapping\Annotation as Darvin;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Page
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Darvin\DefaultValue(sourcePropertyPath="title")
     */
    private $metaTitle;
}
```

__2. Остальное сделает [event subscriber](../../EventListener/DefaultValueSubscriber.php).__

При flush'е сущности «Page» значение свойства «metaTitle», если оно не задано, будет взято из свойства «title».
