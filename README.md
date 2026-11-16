# Collect

Библиотека для удобной работы с массивами и коллекциями данных в PHP.

## Установка

```bash
composer require wiznkingpro/collect
```

## Примеры использования

```php
use function collect;

$collection = collect([1, 2, 3, 4]);

$doubled = $collection->map(function ($item) {
    return $item * 2;
})->all();

$evens = $collection->filter(function ($item) {
    return $item % 2 === 0;
})->all();
```
