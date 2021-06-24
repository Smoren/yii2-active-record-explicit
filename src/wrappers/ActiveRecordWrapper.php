<?php

namespace Smoren\Yii2\ActiveRecordExplicit\wrappers;


use Smoren\Yii2\ActiveRecordExplicit\models\ActiveRecord;
use yii\base\Arrayable;

/**
 * Абстрактный класс для обёртывания ActiveRecord модели
 */
abstract class ActiveRecordWrapper extends BaseWrapper implements Arrayable
{
    /** @var ActiveRecord */
    protected $item;

    /**
     * Показывает, новый ли объект
     * @return bool
     */
    public function isNewRecord(): bool
    {
        return $this->item->isNewRecord;
    }

    /**
     * @inheritDoc
     */
    public function fields()
    {
        return array_merge(array_keys($this->item->attributes));
    }

    /**
     * @inheritDoc
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return $this->item->toArray();
    }
}