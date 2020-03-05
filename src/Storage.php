<?php


namespace Smoren\Yii2\ArrayStorage;


use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Storage
{
    protected $storage;

    /**
     * BaseStorage constructor.
     * @param array|null $storage
     */
    public function __construct(?array &$storage = null)
    {
        $this->storage = &$storage ?? [];
    }

    /**
     * @param string $key
     * @param bool $throwException
     * @return bool
     * @throws Exception
     */
    public function has(string $key, bool $throwException = false): bool
    {
        /** @var mixed $value */
        $value = ArrayHelper::getValue(
            $this->storage, $key, new Exception("key '{$key}' is not exist in user storage", 1)
        );

        $result = !($value instanceof Exception);

        if(!$result && $throwException) {
            /** @var Exception $value */
            throw $value;
        }

        return $result;
    }

    /**
     * @param string|null $key
     * @param null $defaultValue
     * @return array|mixed|null
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
     * @param string $key
     * @param $value
     * @param bool $rewriteExist
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
     * @param string $key
     * @return mixed
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