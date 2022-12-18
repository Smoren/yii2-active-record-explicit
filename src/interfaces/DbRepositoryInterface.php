<?php

namespace Smoren\Yii2\ActiveRecordExplicit\interfaces;

use yii\db\Connection;

interface DbRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Connection
     */
    public function getConnection(): Connection;
}
