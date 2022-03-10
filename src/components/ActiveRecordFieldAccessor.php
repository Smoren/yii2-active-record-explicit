<?php


namespace Smoren\Yii2\ActiveRecordExplicit\components;


use Smoren\Yii2\ActiveRecordExplicit\exceptions\AccessorException;
use yii\db\ActiveRecord;

class ActiveRecordFieldAccessor
{
    /**
     * @var ActiveRecord
     */
    protected $model;
    /**
     * @var string
     */
    protected $fieldName;
    /**
     * @var string[]
     */
    protected $relationPath;

    /**
     * ActiveRecordFieldAccessor constructor.
     * @param ActiveRecord $model
     * @param string $fieldPath
     * @throws AccessorException
     */
    public function __construct(ActiveRecord $model, string $fieldPath)
    {
        $fieldPathExploded = explode('.', $fieldPath);

        $this->model = $model;
        $this->fieldName = array_pop($fieldPathExploded);
        $this->relationPath = $fieldPathExploded;

        $this->check();
    }

    /**
     * @return $this
     * @throws AccessorException
     */
    public function check(): self
    {
        $buf = $this->model;

        foreach($this->relationPath as $relation) {
            if(!isset($buf->{$relation}) || !($buf->{$relation} instanceof ActiveRecord)) {
                throw new AccessorException(
                    "relation {$relation} not found in ".get_class($buf),
                    AccessorException::RELATION_NOT_FOUND,
                    null,
                    [
                        'field' => $relation,
                        'class' => get_class($buf),
                        'model' => $buf,
                    ]
                );
            }
            $buf = $buf->{$relation};
        }

        if(!$buf->hasProperty($this->fieldName)) {
            throw new AccessorException(
                "property {$this->fieldName} not found in ".get_class($buf),
                AccessorException::PROPERTY_NOT_FOUND,
                null,
                [
                    'field' => $this->fieldName,
                    'class' => get_class($buf),
                    'model' => $buf,
                ]
            );
        }

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function get()
    {
        return $this->access();
    }

    /**
     * @param $value
     * @return $this
     */
    public function set($value): self
    {
        $this->access(function(ActiveRecord $model, string $fieldName) use ($value) {
            $model->{$fieldName} = $value;
        });

        return $this;
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->get() === null;
    }

    /**
     * @return bool
     */
    public function isNotNull(): bool
    {
        return !$this->isNull();
    }

    /**
     * @param callable|null $callback
     * @return mixed|null
     */
    protected function access(?callable $callback = null)
    {
        $buf = $this->model;

        if($this->relationPath !== null) {
            foreach($this->relationPath as $relation) {
                $buf = $buf->{$relation};
            }
        }

        if(is_callable($callback)) {
            $callback($buf, $this->fieldName);
        }

        return $buf->{$this->fieldName};
    }
}
