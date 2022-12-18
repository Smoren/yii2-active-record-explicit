<?php

namespace Smoren\Yii2\ActiveRecordExplicit\interfaces;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbConnectionManagerException;
use yii\db\Connection;

interface DbConnectionManagerInterface
{
    /**
     * @param string|null $modelClass
     * @return Connection
     * @throws DbConnectionManagerException
     */
    public function getConnection(?string $modelClass = null): Connection;

    /**
     * @param DbRepositoryInterface $repository
     * @return void
     * @throws DbConnectionManagerException
     */
    public function attachRepository(DbRepositoryInterface $repository): void;

    /**
     * @param DbRepositoryInterface $repository
     * @return void
     * @throws DbConnectionManagerException
     */
    public function detachRepository(DbRepositoryInterface $repository): void;
}
