<?php

namespace Smoren\Yii2\ActiveRecordExplicit\repository;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbConnectionManagerException;
use Smoren\Yii2\ActiveRecordExplicit\interfaces\DbConnectionManagerInterface;
use Smoren\Yii2\ActiveRecordExplicit\interfaces\DbRepositoryInterface;
use yii\db\Connection;

class DbConnectionManager implements DbConnectionManagerInterface
{
    /**
     * @var Connection
     */
    protected $defaultConnection;
    /**
     * @var DbRepositoryInterface[]
     */
    protected $repositoryMap = [];

    /**
     * @param Connection|null $defaultConnection
     */
    public function __construct(?Connection $defaultConnection = null)
    {
        $this->defaultConnection = $defaultConnection;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnection(?string $modelClass = null): Connection
    {
        if($modelClass === null || !isset($this->repositoryMap[$modelClass])) {
            if($this->defaultConnection === null) {
                throw new DbConnectionManagerException(
                    'cannot find connection',
                    DbConnectionManagerException::CANNOT_FIND_CONNECTION
                );
            }
            return $this->defaultConnection;
        }

        return $this->repositoryMap[$modelClass]->getConnection();
    }

    /**
     * {@inheritDoc}
     */
    public function attachRepository(DbRepositoryInterface $repository): void
    {
        $modelClass = $repository->getModelClass();
        if(isset($this->repositoryMap[$modelClass])) {
            throw new DbConnectionManagerException(
                'attach duplicate',
                DbConnectionManagerException::CANNOT_ATTACH_REPOSITORY,
                null,
                ['modelClass' => $modelClass]
            );
        }
        $this->repositoryMap[$modelClass] = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function detachRepository(DbRepositoryInterface $repository): void
    {
        $modelClass = $repository->getModelClass();
        if(!isset($this->repositoryMap[$modelClass])) {
            throw new DbConnectionManagerException(
                'cannot detach not attached repository',
                DbConnectionManagerException::CANNOT_DETACH_REPOSITORY,
                null,
                ['modelClass' => $modelClass]
            );
        }
        unset($this->repositoryMap[$modelClass]);
    }
}
