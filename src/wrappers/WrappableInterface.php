<?php

namespace Smoren\Yii2\ActiveRecordExplicit\wrappers;

/**
 * Интерфейс для оберток
 */
interface WrappableInterface
{
    /**
     * Сохранение сущности
     * @return mixed
     */
    public function save();

    /**
     * Удаление сущности
     * @return mixed
     */
    public function delete();
}
