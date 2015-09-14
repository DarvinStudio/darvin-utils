Flash notifier
==============

## Описание

Flash notifier - сервис для добавления flash-сообщений.

## Использование

Пример:

```php
$flashNotifier = $this->getContainer()->get('darvin_utils.flash.notifier');
$flashNotifier->done(true, 'Success!');
$flashNotifier->error('Error!');
$flashNotifier->formError();
$flashNotifier->success('Success!');
```
