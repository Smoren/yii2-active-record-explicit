<?php


namespace Smoren\Yii2\ActiveRecordExplicit\models;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use yii\base\NotSupportedException;
use yii\db\ColumnSchemaBuilder;
use yii\db\Expression;

/**
 * Class Migration
 */
class Migration extends \yii\db\Migration
{
    /**
     * @return ColumnSchemaBuilder
     * @throws NotSupportedException
     */
    public function uuidPrimaryKey()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid NOT NULL PRIMARY KEY DEFAULT uuid_generate_v4()');
    }

    /**
     * @return ColumnSchemaBuilder
     * @throws NotSupportedException
     */
    public function uuidAutoGen()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid NOT NULL DEFAULT uuid_generate_v4()');
    }

    /**
     * @return ColumnSchemaBuilder
     * @throws NotSupportedException
     */
    public function uuid()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid');
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function alias(bool $unique = true)
    {
        $result = $this->string(32)->notNull();

        if($unique) {
            $result = $result->unique();
        }

        return $result;
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function longAlias(bool $unique = true)
    {
        $result = $this->string(255)->notNull();

        if($unique) {
            $result = $result->unique();
        }

        return $result;
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function name()
    {
        return $this->string(255)->notNull();
    }

    /**
     * @return ColumnSchemaBuilder
     * @throws NotSupportedException
     */
    public function createdAt()
    {
        switch($this->db->driverName) {
            case 'mysql':
                return $this->bigInteger()->notNull()->defaultValue(new Expression('unix_timestamp()'));
            case 'pgsql':
                return $this->bigInteger()->notNull()->defaultValue(new Expression('extract(epoch from now())::INTEGER'));
            default:
                throw new NotSupportedException("unknown driver {$this->db->driverName}", 1);
        }
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function updatedAt()
    {
         return $this->bigInteger();
    }
}