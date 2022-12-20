<?php

namespace Smoren\Yii2\ActiveRecordExplicit\repository;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbConnectionManagerException;
use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use Smoren\Yii2\ActiveRecordExplicit\interfaces\DbConnectionManagerInterface;
use Smoren\Yii2\ActiveRecordExplicit\interfaces\DbRepositoryInterface;
use Smoren\Yii2\ActiveRecordExplicit\models\ActiveQuery;
use Smoren\Yii2\ActiveRecordExplicit\models\ActiveRecord;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\di\NotInstantiableException;
use Yii;

abstract class DbRepository implements DbRepositoryInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param callable|null $filter
     * @param bool $withRelations
     * @return array|ActiveRecord
     * @throws DbConnectionManagerException
     */
    public function findAll(?callable $filter = null, bool $withRelations = true): array
    {
        try {
            $this->activate($withRelations);
            return $this->find($filter)->all();
        } finally {
            $this->deactivate($withRelations);
        }
    }

    /**
     * @param callable|null $filter
     * @param bool $withRelations
     * @return array|ActiveRecord
     * @throws DbConnectionManagerException
     * @throws DbException
     */
    public function findOne(?callable $filter = null, bool $withRelations = true)
    {
        try {
            $this->activate($withRelations);
            return $this->find($filter)->one();
        } finally {
            $this->deactivate($withRelations);
        }
    }

    /**
     * @param callable|null $filter
     * @param bool $withRelations
     * @return ActiveRecord|array
     * @throws DbConnectionManagerException
     * @throws DbException
     */
    public function findFirst(?callable $filter = null, bool $withRelations = true)
    {
        try {
            $this->activate($withRelations);
            return $this->find($filter)->first();
        } finally {
            $this->deactivate($withRelations);
        }
    }

    public function getRelatedModelClasses(): array
    {
        return [];
    }

    /**
     * @param callable|null $filter
     * @return ActiveQuery
     */
    protected function find(?callable $filter = null): ActiveQuery
    {
        $query = $this->getModelClass()::find();

        if($filter !== null) {
            $filter($query);
        }

        return $query;
    }

    /**
     * @param ActiveRecord $model
     * @param bool $withRelations
     * @return void
     * @throws DbConnectionManagerException
     * @throws DbException
     */
    protected function saveModel(ActiveRecord $model, bool $withRelations = true): void
    {
        try {
            $this->activate($withRelations);
            $model->save();
        } finally {
            $this->deactivate($withRelations);
        }
    }

    /**
     * @param ActiveRecord $model
     * @param bool $withRelations
     * @return int
     * @throws DbConnectionManagerException
     * @throws DbException
     */
    protected function deleteModel(ActiveRecord $model, bool $withRelations = true): int
    {
        try {
            $this->activate($withRelations);
            return $model->delete();
        } finally {
            $this->deactivate($withRelations);
        }
    }

    /**
     * @param ActiveRecord $model
     * @param bool $withRelations
     * @return void
     * @throws DbConnectionManagerException
     */
    protected function refreshModel(ActiveRecord $model, bool $withRelations = true): void
    {
        try {
            $this->activate($withRelations);
            $model->refresh();
        } finally {
            $this->deactivate($withRelations);
        }
    }

    /**
     * @param callable $action
     * @param bool $withRelations
     * @return void
     * @throws DbConnectionManagerException
     */
    protected function useConnection(callable $action, bool $withRelations = true): void
    {
        try {
            $this->activate($withRelations);
            $action();
        } finally {
            $this->deactivate();
        }
    }

    /**
     * @param bool $withRelations
     * @return $this
     * @throws DbConnectionManagerException
     */
    protected function activate(bool $withRelations = true): self
    {
        $this->getConnectionManager()->attachRepository($this, $withRelations);
        return $this;
    }

    /**
     * @param bool $withRelations
     * @return $this
     * @throws DbConnectionManagerException
     */
    protected function deactivate(bool $withRelations = true): self
    {
        $this->getConnectionManager()->detachRepository($this, $withRelations);
        return $this;
    }

    /**
     * @return DbConnectionManagerInterface
     * @throws DbConnectionManagerException
     */
    protected function getConnectionManager(): DbConnectionManagerInterface
    {
        try {
            return Yii::$container->get(DbConnectionManagerInterface::class);
        } catch(InvalidConfigException|NotInstantiableException $e) {
            throw new DbConnectionManagerException(
                'cannot instantiate',
                DbConnectionManagerException::CANNOT_INSTANTIATE
            );
        }
    }
}
