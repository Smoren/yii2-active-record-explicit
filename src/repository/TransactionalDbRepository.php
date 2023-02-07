<?php

namespace Smoren\Yii2\ActiveRecordExplicit\repository;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use yii\db\Connection;
use yii\db\Transaction;
use Exception;

/**
 * Transactional repository wrapper
 */
class TransactionalDbRepository
{
    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * TransactionalDBRepository constructor
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Calls callback function wrapped by DB transaction
     * @param callable $callback callback function
     * @return mixed callback result
     * @throws DbException if something went wrong and transaction was rolled back
     */
    public function atomically(callable $callback)
    {
        $transaction = $this->connection->beginTransaction($this->getTransactionLevel());
        try {
            $result = $callback();
            $transaction->commit();
            return $result;
        } catch(DbException $e) {
            $transaction->rollBack();
            throw $e;
        } catch(Exception $e) {
            $transaction->rollBack();
            throw new DbException('transaction exception', DbException::STATUS_UNKNOWN, $e);
        }
    }

    /**
     * Returns transaction level
     * @return string
     */
    protected function getTransactionLevel(): string
    {
        return Transaction::READ_COMMITTED;
    }
}
