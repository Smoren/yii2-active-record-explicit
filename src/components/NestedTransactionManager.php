<?php

namespace Smoren\Yii2\ActiveRecordExplicit\components;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use yii\db\Connection;
use yii\db\Transaction;
use yii\db\Exception;

class NestedTransactionManager
{
    const ON_DESTRUCT_THROW_EXCEPTION = 0;
    const ON_DESTRUCT_COMMIT = 1;
    const ON_DESTRUCT_ROLLBACK = 2;

    const ERROR_CANNOT_COMMIT = 100;
    const ERROR_UNCOMMITTED_TRANSACTIONS = 101;

    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var Transaction[]
     */
    protected $dbTransactions = [];
    /**
     * @var int
     */
    protected $onDestruct;

    /**
     * @param Connection $connection
     * @param bool $onDestruct
     */
    public function __construct(Connection $connection, bool $onDestruct = self::ON_DESTRUCT_THROW_EXCEPTION)
    {
        $this->connection = $connection;
        $this->onDestruct = $onDestruct;
    }

    /**
     * @throws DbException
     */
    public function __destruct()
    {
        switch($this->onDestruct) {
            case self::ON_DESTRUCT_COMMIT:
                $this->commitAll();
                break;
            case self::ON_DESTRUCT_ROLLBACK:
                $this->rollbackAll();
                break;
            default:
                if(count($this->dbTransactions)) {
                    throw new DbException(
                        'transaction exception',
                        self::ERROR_UNCOMMITTED_TRANSACTIONS
                    );
                }
        }
    }

    /**
     * @param string $isolationLevel
     * @return Transaction
     */
    public function start(string $isolationLevel = Transaction::READ_COMMITTED): Transaction
    {
        $transaction = $this->pushDbTransaction($isolationLevel);
        $this->onStart(count($this->dbTransactions));
        return $transaction;
    }

    /**
     * @return bool
     * @throws DbException
     */
    public function commit(): bool
    {
        $result = false;
        $transactionLevel = count($this->dbTransactions);

        $this->onBeforeCommit($transactionLevel);

        if(($dbTransaction = $this->popDbTransaction()) !== null) {
            try {
                $dbTransaction->commit();
                $result = true;
            } catch(Exception $e) {
                $this->onCommitError($transactionLevel, $e);

                throw new DbException(
                    'transaction exception',
                    self::ERROR_CANNOT_COMMIT,
                    $e
                );
            }
        }

        $this->onCommitSuccess($transactionLevel);

        return $result;
    }

    /**
     * @return bool
     */
    public function rollback(): bool
    {
        $result = false;
        $transactionLevel = count($this->dbTransactions);

        $this->onBeforeRollback($transactionLevel);

        if(($dbTransaction = $this->popDbTransaction()) !== null) {
            $dbTransaction->rollBack();
            $result = true;
        }

        $this->onAfterRollback($transactionLevel);

        return $result;
    }

    /**
     * @return int
     * @throws DbException
     */
    public function commitAll(): int
    {
        $result = 0;

        while($this->commit()) {
            ++$result;
        }

        return $result;
    }

    /**
     * @return int
     */
    public function rollbackAll(): int
    {
        $result = 0;

        while($this->rollback()) {
            ++$result;
        }

        return $result;
    }

    /**
     * @param int $transactionLevel
     * @return void
     * @override
     */
    protected function onStart(int $transactionLevel): void
    {

    }

    /**
     * @param int $transactionLevel
     * @return void
     * @override
     */
    protected function onBeforeCommit(int $transactionLevel): void
    {

    }

    /**
     * @param int $transactionLevel
     * @return void
     * @override
     */
    protected function onCommitSuccess(int $transactionLevel): void
    {

    }

    /**
     * @param int $transactionLevel
     * @param Exception $e
     * @return void
     * @override
     */
    protected function onCommitError(int $transactionLevel, Exception $e): void
    {

    }

    /**
     * @param int $transactionLevel
     * @return void
     * @override
     */
    protected function onBeforeRollback(int $transactionLevel): void
    {

    }

    /**
     * @param int $transactionLevel
     * @return void
     * @override
     */
    protected function onAfterRollback(int $transactionLevel): void
    {

    }

    /**
     * @return Transaction|null
     */
    protected function popDbTransaction(): ?Transaction
    {
        return count($this->dbTransactions)
            ? array_pop($this->dbTransactions)
            : null;
    }

    /**
     * @param string $isolationLevel
     * @return Transaction
     */
    protected function pushDbTransaction(string $isolationLevel): Transaction
    {
        $transaction = $this->connection->beginTransaction($isolationLevel);
        $this->dbTransactions[] = $transaction;
        return $transaction;
    }
}
