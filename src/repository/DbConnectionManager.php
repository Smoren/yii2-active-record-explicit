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
     * @param string|null $modelClass
     * @return bool
     */
    public function hasConnection(?string $modelClass = null): bool
    {
        return isset($this->repositoryMap[$modelClass]) || $this->defaultConnection !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function attachRepository(DbRepositoryInterface $repository, bool $withRelations): void
    {
        $modelClass = $repository->getModelClass();
        $this->checkClassDetached($modelClass);
        $this->repositoryMap[$modelClass] = $repository;

        if($withRelations) {
            foreach($repository->getRelatedModelClasses() as $modelClass) {
                $this->checkClassDetached($modelClass);
                $this->repositoryMap[$modelClass] = $repository;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detachRepository(DbRepositoryInterface $repository, bool $withRelations): void
    {
        $modelClass = $repository->getModelClass();
        $this->checkClassAttached($modelClass);
        unset($this->repositoryMap[$modelClass]);

        if($withRelations) {
            foreach($repository->getRelatedModelClasses() as $modelClass) {
                $this->checkClassAttached($modelClass);
                unset($this->repositoryMap[$modelClass]);
            }
        }
    }

    /**
     * @param string $modelClass
     * @return void
     * @throws DbConnectionManagerException
     */
    protected function checkClassAttached(string $modelClass): void
    {
        if(!isset($this->repositoryMap[$modelClass])) {
            throw new DbConnectionManagerException(
                'cannot detach not attached repository',
                DbConnectionManagerException::CANNOT_DETACH_REPOSITORY,
                null,
                ['modelClass' => $modelClass]
            );
        }
    }

    /**
     * @param string $modelClass
     * @return void
     * @throws DbConnectionManagerException
     */
    protected function checkClassDetached(string $modelClass): void
    {
        if(isset($this->repositoryMap[$modelClass])) {
            throw new DbConnectionManagerException(
                'attach duplicate',
                DbConnectionManagerException::CANNOT_ATTACH_REPOSITORY,
                null,
                ['modelClass' => $modelClass]
            );
        }
    }
}
