# yii2-array-storage
Класс для хранения и управления данными в массиве.
Может быть применен на практике, например, в качестве хэлпера для работы 
с полем, хранящим _json_ в модели _ActiveRecord_.

### Установка в проект на Yii2
```
composer require smoren/yii2-array-storage
```

### Примеры использования

```php
<?php

use Smoren\Yii2\ArrayStorage\Storage;

// исходный массив
$data = [
    'a' => [
        'b1' => [1, 2, 3],
        'b2' => 5,
    ]
];

// инициализация хранилища
$storage = new Storage($data);

// получение всего массива данных хранилища
$value = $storage->get();
print_r($value);
/*
Array
(
    [a] => Array
        (
            [b1] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                )
            [b2] => 5
        )
)
*/

// получение элемента по ключу
$value = $storage->get('a');
print_r($value);
/*
Array
(
    [b1] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
        )
    [b2] => 5
)
*/

// получение элемента по ключу с уровнями вложенномти
$value = $storage->get('a.b1.0');
var_dump($value);
/* int(1) */

// попытка получения значения по отсутствующему в хранилище ключу со значением по умолчанию
$value = $storage->get('a.b3', 'Значение по умолчанию');
var_dump($value);
/* string(40) "Значение по умолчанию" */

// попытка получения значения по отсутствующему в хранилище ключу без значения по умолчанию
try {
    $storage->get('a.b3');
} catch(\yii\base\Exception $e) {
    var_dump($e->getMessage());
    /* string(39) "key 'a.b3' is not exist in user storage" */
}

// проверки существования ключа в массиве
var_dump($storage->has('a.b2'));
/* bool(true) */
var_dump($storage->has('a.b3'));
/* bool(false) */
try {
    $storage->has('a.b3', true);
} catch(\yii\base\Exception $e) {
    var_dump($e->getMessage());
    /* string(39) "key 'a.b3' is not exist in user storage" */
}

// добавление элемента в хранилище
$storage->set('a.new', 'Значение нового элемента');
print_r($storage->get());
/*
Array
(
    [a] => Array
        (
            [b1] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                )
            [b2] => 5
            [new] => Значение нового элемента
        )
)
*/

// добавление элемента в хранилище с запретом перезаписи
$storage->set('a.new_another', 'Еще одно значение нового элемента', false);
print_r($storage->get());
/*
Array
(
    [a] => Array
        (
            [b1] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                )
            [b2] => 5
            [new] => Значение нового элемента
            [new_another] => Еще одно значение нового элемента
        )
)
*/

// попытка перезаписи элемента с запретом перезаписи
try {
    $storage->set('a.b2', 'Это значение не запишется', false);
} catch(\yii\base\Exception $e) {
    var_dump($e->getMessage());
    /* string(43) "key 'a.b2' is already exist in user storage" */
}
print_r($storage->get());
/*
Array
(
    [a] => Array
        (
            [b1] => Array
                (
                    [0] => 1
                    [1] => 2
                    [2] => 3
                )
            [b2] => 5
            [new] => Значение нового элемента
            [new_another] => Еще одно значение нового элемента
        )
)
*/

// удаление элемента из хранилища по ключу, получение значения удаленного элемента
$removedValue = $storage->remove('a.b1');
print_r($removedValue);
/*
Array
(
    [0] => 1
    [1] => 2
    [2] => 3
)
*/
print_r($storage->get());
/*
Array
(
    [a] => Array
        (
            [b2] => 5
            [new] => Значение нового элемента
            [new_another] => Еще одно значение нового элемента
        )
)
*/

// попытка удаления несуществующего элемента
try {
    $storage->remove('a.с');
} catch(\yii\base\Exception $e) {
    var_dump($e->getMessage());
    /* string(39) "key 'a.с' is not exist in user storage" */
}
```
