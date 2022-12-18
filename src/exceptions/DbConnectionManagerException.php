<?php

namespace Smoren\Yii2\ActiveRecordExplicit\exceptions;

use Smoren\ExtendedExceptions\BaseException;

class DbConnectionManagerException extends BaseException
{
    public const CANNOT_INSTANTIATE = 1;
    public const CANNOT_ATTACH_REPOSITORY = 2;
    public const CANNOT_DETACH_REPOSITORY = 3;
    public const CANNOT_FIND_CONNECTION = 4;
}
