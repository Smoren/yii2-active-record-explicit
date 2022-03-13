<?php


namespace Smoren\Yii2\ActiveRecordExplicit\components;


use Smoren\Yii2\ActiveRecordExplicit\exceptions\TransactionLogicException;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\Transaction;

class TransactionManager
{
    /**
     * @var Transaction|null
     */
    protected $transaction;
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var bool
     */
    protected $commitOnDestruct;
    /**
     * @var string
     */
    protected $transactionType;

    /**
     * TransactionalTrait constructor.
     * @param Connection $connection
     * @param bool $commitOnDestruct
     * @param string $transactionType
     */
    public function __construct(
        Connection $connection,
        bool $commitOnDestruct = false,
        string $transactionType = Transaction::READ_COMMITTED
    )
    {
        $this->connection = $connection;
        $this->commitOnDestruct = $commitOnDestruct;
        $this->transactionType = $transactionType;
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    public function init(): self
    {
        if($this->isNotStarted()) {
            $this->start();
        }

        return $this;
    }

    /**
     * @param bool|null $commitOnDestruct
     * @param string|null $transactionType
     * @return $this
     * @throws TransactionLogicException
     */
    public function initWithSubTransaction(?bool $commitOnDestruct, ?string $transactionType): self
    {
        if($this->isNotStarted()) {
            $this->start();
        }

        return new TransactionManager(
            $this->connection,
            $commitOnDestruct ?? $this->commitOnDestruct,
            $transactionType ?? $this->transactionType
        );
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    public function start(): self
    {
        $this->checkNotStarted();
        $this->transaction = $this->connection->beginTransaction($this->transactionType);

        return $this;
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    public function commit(): self
    {
        $this->checkStarted();

        try {
            $this->transaction->commit();
        } catch(Exception $e) {
            throw new TransactionLogicException(
                'cannot commit transaction',
                TransactionLogicException::CANNOT_COMMIT_TRANSACTION,
                $e
            );
        }

        $this->transaction = null;

        return $this;
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    public function rollback(): self
    {
        $this->checkStarted();

        $this->transaction->rollBack();
        $this->transaction = null;

        return $this;
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    public function commitIfStarted(): self
    {
        if($this->isStarted()) {
            $this->commit();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    public function rollbackIfStarted(): self
    {
        if($this->isStarted()) {
            $this->rollback();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->transaction instanceof Transaction;
    }

    /**
     * @return bool
     */
    public function isNotStarted(): bool
    {
        return !($this->transaction instanceof Transaction);
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    protected function checkStarted(): self
    {
        if($this->isNotStarted()) {
            throw new TransactionLogicException(
                'transaction already started',
                TransactionLogicException::NOT_STARTED_YET
            );
        }

        return $this;
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    protected function checkNotStarted(): self
    {
        if($this->isStarted()) {
            throw new TransactionLogicException(
                'transaction already started',
                TransactionLogicException::ALREADY_STARTED
            );
        }

        return $this;
    }

    /**
     * @throws TransactionLogicException
     */
    public function __destruct()
    {
        if($this->transaction instanceof Transaction) {
            if($this->commitOnDestruct) {
                $this->commit();
            } else {
                $this->rollback();
            }
        }
    }
}
