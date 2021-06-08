<?php

namespace Smoren\Yii2\ActiveRecordExplicit\wrappers;


use Smoren\ExtendedExceptions\LogicException;

/**
 * Интерфейс для обертки
 * Interface WrapperInterface
 * @package Smoren\Yii2\Microcore\base
 */
abstract class BaseWrapper
{
    /** @var WrappableInterface */
    protected $item;

    /**
     * Создает новый экземпляр сущности и оборачивает ее
     * @param array $data данные для сохранения
     * @param bool $save Нужно ли сохранять в БД
     * @return BaseWrapper
     */
    abstract public static function create(array $data, bool $save = false): self;

    /**
     * Сохраняет обернутый элемент
     * @return BaseWrapper
     */
    abstract public function save(): self;

    /**
     * Удаляет обернутый элемент
     * @return BaseWrapper
     */
    abstract public function delete(): self;

    /**
     * Показывает, новый ли объект
     * @return bool
     */
    abstract public function isNewRecord(): bool;

    /**
     * Создает новые экземпляры сущностей массово
     * @param array $itemsData
     * @param bool $save Нужно ли сохранять в БД
     * @return array
     */
    public static function createCollection(array $itemsData, bool $save = false): array
    {
        $result = [];

        foreach($itemsData as $data) {
            $result[] = static::create($data, $save);
        }

        return $result;
    }

    /**
     * Возвращает обернутый элемент
     * @return WrappableInterface
     */
    public function getItem(): WrappableInterface
    {
        return $this->item;
    }

    /**
     * Оборачивает сущность
     * @param WrappableInterface $item Объект AR модели для оборачивания
     * @return BaseWrapper
     */
    public static function wrapItem(WrappableInterface $item): self
    {
        return new static($item);
    }

    /**
     * Оборачивает коллекцию
     * @param WrappableInterface[] $collection Список объектоы AR моделрй для оборачивания
     * @return BaseWrapper[]
     */
    public static function wrapCollection(array $collection): array
    {
        $result = [];
        foreach($collection as $item) {
            $result[] = static::wrapItem($item);
        }

        return $result;
    }

    /**
     * Проверяет, была ли обернутая сущность сохранена и/или не удалена
     * @return BaseWrapper
     * @throws LogicException
     */
    public function checkIsSaved(): self
    {
        if($this->isNewRecord()) {
            throw new LogicException('item is not saved', DbException::STATUS_LOGIC_ERROR);
        }

        return $this;
    }

    /**
     * BaseWrapper constructor
     * @param WrappableInterface $item Объект AR модели для оборачивания
     */
    public function __construct(WrappableInterface $item)
    {
        $this->item = $item;
    }
}