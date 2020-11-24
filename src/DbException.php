<?php


namespace Smoren\Yii2\ActiveRecordExplicit;


use Smoren\ExtendedExceptions\BaseException;

class DbException extends BaseException
{
    const STATUS_UNKNOWN = -1;
    const STATUS_EMPTY_RESULT = 1;
    const STATUS_REDUDANT_RESULT = 2;
    const STATUS_CANNOT_SAVE_INSTANCE = 3;
    const STATUS_CANNOT_DELETE_INSTANCE = 4;
    const STATUS_LOGIC_ERROR = 5;
}