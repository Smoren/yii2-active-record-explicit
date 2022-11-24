<?php

namespace Smoren\Yii2\ActiveRecordExplicit\behaviors;

use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\helpers\StringHelper;

class AttributeTypecastBehavior extends \yii\behaviors\AttributeTypecastBehavior
{
    /**
     * @var bool
     */
    public $typecastBeforeValidate = false;
    /**
     * @var bool
     */
    public $strict = false;

    /**
     * {@inheritDoc}
     */
    public function events(): array
    {
        $events = parent::events();
        if($this->typecastBeforeValidate) {
            $events[Model::EVENT_BEFORE_VALIDATE] = 'beforeValidate';
        }
        return $events;
    }

    /**
     * Handles owner 'beforeValidate' event, ensuring attribute typecasting.
     * @param Event $event event instance.
     */
    public function beforeValidate(Event $event)
    {
        $this->typecastAttributes();
    }

    /**
     * {@inheritDoc}
     */
    protected function typecastValue($value, $type)
    {
        if($value === null) {
            return null;
        }

        if(is_scalar($type)) {
            if(is_object($value) && method_exists($value, '__toString')) {
                $value = $value->__toString();
            }

            switch($type) {
                case self::TYPE_INTEGER:
                    return $this->typecastInteger($value);
                case self::TYPE_FLOAT:
                    return $this->typecastFloat($value);
                case self::TYPE_BOOLEAN:
                    return $this->typecastBoolean($value);
                case self::TYPE_STRING:
                    return $this->typecastString($value);
                default:
                    throw new InvalidArgumentException("Unsupported type '{$type}'");
            }
        }

        return call_user_func($type, $value);
    }

    /**
     * @param $value
     * @return int|null
     */
    protected function typecastInteger($value): ?int
    {
        if($this->strict && !is_numeric($value)) {
            return null;
        }
        return (int)$value;
    }

    /**
     * @param $value
     * @return float|null
     */
    protected function typecastFloat($value): ?float
    {
        if($this->strict && !is_numeric($value)) {
            return null;
        }
        return (float)$value;
    }

    /**
     * @param $value
     * @return bool|null
     */
    protected function typecastBoolean($value): ?bool
    {
        if($this->strict && !is_bool($value) && !in_array((string)$value, ['1', '0', ''], true)) {
            return null;
        }

        return (bool)$value;
    }

    /**
     * @param $value
     * @return string|null
     */
    protected function typecastString($value): ?string
    {
        if(is_float($value)) {
            return StringHelper::floatToString($value);
        }
        return (string)$value;
    }
}
