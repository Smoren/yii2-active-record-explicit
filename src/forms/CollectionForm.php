<?php

namespace Smoren\Yii2\ActiveRecordExplicit\forms;

use Smoren\Yii2\ActiveRecordExplicit\models\Model;

/**
 * Class CollectionForm
 */
abstract class CollectionForm extends Model
{
    /**
     * @var array
     */
    public $storage;

    /**
     * @var ItemForm[]
     */
    public $validated;

    /**
     * @inheritDoc
     */
    public function load($data, $formName = null)
    {
        $this->storage = $data;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['storage'], 'required'],
            [['storage'], 'validateStorage'],
        ];
    }

    /**
     * @param $attribute
     */
    public function validateStorage($attribute)
    {
        $result = [];
        foreach($this->storage as $item) {
            $form = $this->getItemValidatorForm();
            $form->load($item);
            if(!$form->validate()) {
                foreach($form->errors as $error) {
                    $this->addError($attribute, $error);
                }
            } else {
                $result[] = $form;
            }
        }

        $this->validated = $result;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'storage' => 'Массив данных',
            'validated' => 'Массив форм, прошедших валидацию',
        ];
    }

    /**
     * @return ItemForm
     */
    abstract protected function getItemValidatorForm(): ItemForm;
}
