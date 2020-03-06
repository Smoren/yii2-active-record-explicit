<?php


namespace Smoren\Yii2\ArrayStorage;


use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Класс для управления данными в массиве-хранилище
 * @package Smoren\Yii2\ArrayStorage
 */
class Storage
{
    /**
     * @var array массив данных хранилища
     */
    protected $storage;

    /**
     * BaseStorage constructor.
     * @param array|null $storage исходный массив хранилища с доступом по ссылке
     */
    public function __construct(?array &$storage = null)
    {
        $this->storage = &$storage ?? [];
    }

    /**
     * Проверить наличие ключа в хранилище (вложенность разделяется точками)
     * @param string $key ключ в хранилище. Например: 'a.b.c.0' соответствует $this->storage['a']['b']['c'][0]
     * @param bool $throwException
     * @return bool
     * @throws Exception
     */
    public function has(string $key, bool $throwException = false): bool
    {
        $value = ArrayHelper::getValue($this->storage, $key, INF);
        $result = !($value == INF);

        if(!$result && $throwException) {
            throw new Exception("key '{$key}' is not exist in user storage", 1);
        }

        return $result;
    }

    /**
     * Получить значение их хранилища по его ключу, либо все хранилище, если ключ не указан
     * @param string|null $key ключ в хранилище. Например: 'a.b.c.0' соответствует $this->storage['a']['b']['c'][0]
     * @param null $defaultValue значение по умолчанию
     * @return mixed
     * @throws Exception
     */
    public function get(?string $key = null, $defaultValue = null)
    {
        if($key === null) {
            return $this->storage;
        }

        try {
            $this->has($key, true);
            return ArrayHelper::getValue($this->storage, $key);
        } catch(Exception $e) {
            if($defaultValue === null) {
                throw $e;
            } else {
                return $defaultValue;
            }
        }
    }

    /**
     * Записать в хранилище значение по ключу
     * @param string $key ключ в хранилище. Например: 'a.b.c.0' соответствует $this->storage['a']['b']['c'][0]
     * @param mixed $value значение
     * @param bool $rewriteExist перезаписывать ли существующие данные или бросить исключение
     * @return Storage
     * @throws Exception
     */
    public function set(string $key, $value, bool $rewriteExist = true): self
    {
        if(!$rewriteExist && $this->has($key)) {
            throw new Exception("key '{$key}' is already exist in user storage", 1);
        }

        ArrayHelper::setValue($this->storage, $key, $value);

        return $this;
    }

    /**
     * Удалить из хранилища значение по ключу
     * @param string $key ключ в хранилище. Например: 'a.b.c.0' соответствует $this->storage['a']['b']['c'][0]
     * @return mixed удаленное значение
     * @throws Exception
     */
    public function remove(string $key)
    {
        $this->has($key, true);

        $arKey = explode('.', $key);
        $lastKey = array_pop($arKey);
        $subStorage = &$this->storage;
        foreach($arKey as $k) {
            $subStorage = &$subStorage[$k];
        }

        $value = $subStorage[$lastKey];
        unset($subStorage[$lastKey]);

        return $value;
    }

    /**
     * Очистить хранилище
     * @return $this
     */
    public function clear(): self
    {
        foreach($this->storage as $key => $value) {
            unset($this->storage[$key]);
        }

        return $this;
    }
}