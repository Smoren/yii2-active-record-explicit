<?php

namespace Smoren\Yii2\ActiveRecordExplicit\behaviors;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use Smoren\Yii2\ActiveRecordExplicit\models\ActiveRecord;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\BaseActiveRecord;

/**
 * Поведение позволяет задать аттрибуты AR модели только для чтения.
 */
class ReadonlyBehavior extends Behavior
{
    /**
     * @var array Список аттрибутов
     */
    public $attributes;

    /**
     * Список
     * @return array
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'checkIt',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'checkIt',
        ];
    }

    /**
     * Проверяем, что значения изменились.
     * @param Event $event
     * @throws DbException
     */
    public function checkIt(Event $event): void
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;

        foreach($this->attributes as $attribute) {
            if($model->getAttribute($attribute) != $model->getOldAttribute($attribute)) {
                throw new DbException("you cannot change {$attribute} attribute", DbException::STATUS_LOGIC_ERROR);
            }
        }
    }
}
