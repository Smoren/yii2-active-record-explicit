<?php

namespace Smoren\Yii2\ActiveRecordExplicit\wrappers;

/**
 * Интерфейс для оберток
 */
interface WrappableInterface
{
    /**
     * Создание сущности
     * @param array $data
     * @param bool $save
     * @return static
     */
    public static function create(array $data, bool $save = false): self;

    /**
     * Оборачивает сущность
     * @param WrappableInterface $item Объект AR модели для оборачивания
     * @return WrappableInterface
     */
    public static function wrapItem(WrappableInterface $item): self;

    /**
     * Оборачивает коллекцию
     * @param WrappableInterface[] $collection Список объектоы AR моделрй для оборачивания
     * @return BaseWrapper[]
     */
    public static function wrapCollection(array $collection): array;

    /**
     * Сохранение сущности
     * @return $this
     */
    public function save(): self;

    /**
     * Проверка на то, новая ли сущность
     * @return bool
     */
    public function isNewRecord(): bool;

    /**
     * Удаление сущности
     * @return mixed
     */
    public function delete(): self;
}