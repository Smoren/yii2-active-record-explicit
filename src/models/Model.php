<?php

namespace Smoren\Yii2\ActiveRecordExplicit\models;

/**
 * Класс для модели ввода данных для REST API
 */
abstract class Model extends \yii\base\Model
{
    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * @param array $data
     * @param null $formName
     * @return bool|void
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
    }
}
