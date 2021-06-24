<?php

namespace Smoren\Yii2\ActiveRecordExplicit\models;

/**
 * Класс для модели ввода данных для REST API
 */
abstract class Model extends \yii\base\Model
{
    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @param array $input
     * @return static
     */
    public static function create(array $input): self
    {
        $form = new static();
        $form->load($input);

        return $form;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function load($data, $formName = null)
    {
        $keys = array_keys($this->getAttributes());

        $cleanParams = [];
        foreach($keys as $key) {
            if(!is_array($data) || !array_key_exists($key, $data)) {
                continue;
            }
            $cleanParams[$key] = $data[$key];
        }

        parent::load($cleanParams, $formName);

        return true;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $result = parent::validate($attributeNames, $clearErrors);
        $this->updateStatusCode();

        return $result;
    }

    protected function updateStatusCode(): self
    {
        if(count($this->errors)) {
            $this->statusCode = 406;
        } else {
            $this->statusCode = 200;
        }

        return $this;
    }
}
