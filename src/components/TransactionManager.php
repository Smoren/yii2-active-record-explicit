<?php


namespace Smoren\Yii2\ActiveRecordExplicit\components;


use Smoren\Yii2\ActiveRecordExplicit\exceptions\TransactionLogicException;
use Smoren\Yii2\ActiveRecordExplicit\models\ActiveRecord;
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
     * @var ActiveRecord|null
     */
    protected $model;
    /**
     * @var bool|null
     */
    protected $modelIsNewRecordOnStart;

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
     * @param ActiveRecord $model
     * @return $this
     */
    public function linkModel(ActiveRecord $model): self
    {
        $this->model = $model;
        $this->modelIsNewRecordOnStart = $model->isNewRecord;
        return $this;
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
    public function initWithSubTransaction(?bool $commitOnDestruct = null, ?string $transactionType = null): self
    {
        if($this->isNotStarted()) {
            $this->start();
        }

        $subTransaction = new TransactionManager(
            $this->connection,
            $commitOnDestruct ?? $this->commitOnDestruct,
            $transactionType ?? $this->transactionType
        );

        if($this->hasLinkedModel()) {
            $subTransaction->linkModel($this->model);
        }

        return $subTransaction->start();
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

        if($this->hasLinkedModel()) {
            $this->modelIsNewRecordOnStart = $this->model->isNewRecord;
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

        if($this->hasLinkedModel() && (bool)$this->model->isNewRecord !== (bool)$this->modelIsNewRecordOnStart) {
            $this->model->isNewRecord = $this->modelIsNewRecordOnStart;
        }

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
     * @return bool
     * @throws TransactionLogicException
     */
    public function isNewRecord(): bool
    {
        if(!$this->hasLinkedModel()) {
            throw new TransactionLogicException(
                'no linked model',
                TransactionLogicException::NO_LINKED_MODEL
            );
        }

        return $this->modelIsNewRecordOnStart;
    }

    /**
     * @return $this
     * @throws TransactionLogicException
     */
    protected function checkStarted(): self
    {
        if($this->isNotStarted()) {
            throw new TransactionLogicException(
                'transaction is not started yet',
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
                'transaction is already started',
                TransactionLogicException::ALREADY_STARTED
            );
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasLinkedModel(): bool
    {
        return $this->model !== null;
    }

    /**
     * @throws TransactionLogicException
     */
    public function __destruct()
    {
        if($this->transaction instanceof Transaction) {
            if($this->hasLinkedModel()) {
                $this->model = null;
            }

            if($this->commitOnDestruct) {
                $this->commit();
            } else {
                $this->rollback();
            }
        }
    }
}
