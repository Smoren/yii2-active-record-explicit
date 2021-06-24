<?php


namespace Smoren\Yii2\ActiveRecordExplicit\helpers;


use Smoren\Yii2\ActiveRecordExplicit\models\Model;

class FormValidator
{
    /**
     * @param Model $form
     * @param string $exceptionClass
     * @param int $code
     * @param string $message
     */
    public static function validate(Model $form, string $exceptionClass, int $code, string $message = "validation error")
    {
        if(!$form->validate()) {
            throw new $exceptionClass($message, $code, null, $form->errors);
        }
    }
}