<?php


namespace Smoren\Yii2\ActiveRecordExplicit\traits;


use Smoren\Yii2\ActiveRecordExplicit\components\TransactionManager;
use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use Smoren\Yii2\ActiveRecordExplicit\exceptions\TransactionLogicException;
use Smoren\Yii2\ActiveRecordExplicit\models\ActiveRecord;
use Yii;

/**
 * Trait TransactionalTrait
 * @package app\modules\securator\traits
 *
 * @property ActiveRecord $this
 * @property TransactionManager $transactionManager
 */
trait ActiveRecordTransactionalTrait
{
    /**
     * @var TransactionManager
     */
    protected $_transactionManager;

    /**
     * @return TransactionManager
     */
    public function getTransactionManager(): TransactionManager
    {
        if($this->_transactionManager === null) {
            $this->_transactionManager = new TransactionManager(Yii::$app->db);
            /** @var ActiveRecord $this */
            $this->_transactionManager->linkModel($this);
        }

        return $this->_transactionManager;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws TransactionLogicException
     * @throws DbException
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        try {
            $result = parent::save($runValidation, $attributeNames);
            $this->getTransactionManager()->commitIfStarted();
        } catch(DbException $e) {
            $this->getTransactionManager()->rollbackIfStarted();
            throw $e;
        }

        return $result;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws DbException
     */
    public function preSave(bool $runValidation = true, $attributeNames = null): bool
    {
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @return false|int
     * @throws DbException
     * @throws TransactionLogicException
     */
    public function delete()
    {
        try {
            $result = parent::delete();
            $this->getTransactionManager()->commitIfStarted();
        } catch(DbException $e) {
            $this->getTransactionManager()->rollbackIfStarted();
            throw $e;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws TransactionLogicException
     */
    public function isNewRecordInTransaction(): bool
    {
        return $this->getTransactionManager()->isNewRecord();
    }
}
