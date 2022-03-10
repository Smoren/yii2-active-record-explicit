<?php


namespace Smoren\Yii2\ActiveRecordExplicit\exceptions;


use Smoren\ExtendedExceptions\LogicException;

class AccessorException extends LogicException
{
    const RELATION_NOT_FOUND = 1;
    const PROPERTY_NOT_FOUND = 2;
}
