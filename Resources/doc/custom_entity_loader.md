Custom entity loader
====================

## Описание

Custom entity loader - сервис для инициализации одной сущности в поле другой, при этом информация для инициализации
(класс, название свойства, значение свойства) берется из свойств последней.

## Использование

__1. Помечаем свойство, в котором должна быть инициализирована сущность, аннотацией «\Darvin\Utils\Mapping\Annotation\CustomObject».__

Аргументы аннотации:

- __class__ - название класса инициализируемой сущности;
- __classPropertyPath__ - путь, по которому хранится название класса инициализируемой сущности;
- __initProperty__ - название свойства инициализируемой сущности, по которому ее нужно искать;
- __initPropertyValuePath__ - путь, по которому хранится значение свойства инициализируемой сущности, по которому ее нужно искать.

(!) Первые два аргумента взаимоисключающие.

(!) Свойство в «initProperty» должно быть уникальным (например, идентификатором).

Пример:

```php
use AppBundle\Entity\Post\Post;
use Darvin\Utils\Mapping\Annotation as Darvin;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Page
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $postId;

    /**
     * @var \AppBundle\Entity\Post\Post
     *
     * @Darvin\CustomObject(class="AppBundle\Entity\Post\Post", initProperty="id", initPropertyValuePath="postId")
     */
    private $post;
}
```

__2. Используем метод «loadForObject()» (для одной сущности) или «loadForObjects()» (для массива) сервиса
«darvin_utils.custom_object.loader.entity»:__

```php
$page = new Page();
$page->setPostId(1);
$this->getContainer()->get('darvin_utils.custom_object.loader.entity')->loadForObject($page);
```

Из свойства «Page::$postId» будет вытащено значение свойства «\AppBundle\Entity\Post\Post::$id», затем по этому значению
будет произведен поиск сущности «\AppBundle\Entity\Post\Post». Найденная сущность будет помещена в «Page::$post».
