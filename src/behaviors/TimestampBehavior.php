<?php


namespace Smoren\Yii2\ActiveRecordExplicit\behaviors;


use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

class TimestampBehavior extends Behavior
{
    /**
     * @var string
     */
    public $createdAtField = 'created_at';
    /**
     * @var string
     */
    public $updatedAtField = 'updated_at';
    /**
     * @var int|null
     */
    public $currentTime = null;
    /**
     * @var ActiveRecord
     */
    public $owner;

    /**
     * Список
     * @return array
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
        ];
    }

    /**
     * При сохранении новой модели
     */
    public function beforeInsert()
    {
        if(
            $this->createdAtField !== null &&
            $this->owner->hasAttribute($this->createdAtField)
        ) {
            $this->owner->{$this->createdAtField} = $this->currentTime ?? time();
        }
    }

    /**
     * При сохранении существующей модели
     */
    public function beforeUpdate()
    {
        if(
            $this->updatedAtField !== null &&
            $this->owner->hasAttribute($this->updatedAtField) &&
            count($this->owner->getDirtyAttributes())
        ) {
            $this->owner->{$this->updatedAtField} = $this->currentTime ?? time();
        }
    }
}
