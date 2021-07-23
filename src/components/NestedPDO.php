<?php


namespace Smoren\Yii2\ActiveRecordExplicit\components;


use PDO;

class NestedPDO extends PDO
{
    /**
     * @var string[] Database drivers that support SAVEPOINTs
     */
    protected static $savepointTransactions = ["pgsql", "mysql"];

    /**
     * @var int The current transaction level
     */
    protected $transLevel = 0;

    /**
     * @inheritDoc
     */
    public function beginTransaction()
    {
        if($this->transLevel == 0 || !$this->nestable()) {
            parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
        }

        $this->transLevel++;
    }

    /**
     * @inheritDoc
     */
    public function commit()
    {
        $this->transLevel--;

        if($this->transLevel == 0 || !$this->nestable()) {
            parent::commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->transLevel}");
        }
    }

    /**
     * @inheritDoc
     */
    public function rollBack()
    {
        $this->transLevel--;

        if($this->transLevel == 0 || !$this->nestable()) {
            parent::rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
        }
    }

    /**
     * @return bool
     */
    protected function nestable()
    {
        return in_array($this->getAttribute(PDO::ATTR_DRIVER_NAME), self::$savepointTransactions);
    }
}