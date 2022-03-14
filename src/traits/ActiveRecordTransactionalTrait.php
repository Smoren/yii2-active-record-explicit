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
 */
trait ActiveRecordTransactionalTrait
{
    /**
     * @var TransactionManager
     */
    protected $transactionManager;

    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        parent::init();
        $this->transactionManager = new TransactionManager(Yii::$app->db);
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
            $this->transactionManager->commitIfStarted();
        } catch(DbException $e) {
            $this->transactionManager->rollbackIfStarted();
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
            $this->transactionManager->commitIfStarted();
        } catch(DbException $e) {
            $this->transactionManager->rollbackIfStarted();
            throw $e;
        }

        return $result;
    }
}
