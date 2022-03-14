<?php


namespace Smoren\Yii2\ActiveRecordExplicit\exceptions;


use Smoren\ExtendedExceptions\LogicException;

/**
 * Class TransactionLogicException
 * @package Smoren\Yii2\ActiveRecordExplicit\exceptions
 */
class TransactionLogicException extends LogicException
{
    const ALREADY_STARTED = 1;
    const NOT_STARTED_YET = 2;
    const CANNOT_COMMIT_TRANSACTION = 3;
    const NO_LINKED_MODEL = 4;
}
