<?php


namespace Smoren\Yii2\ActiveRecordExplicit\models;

use yii\base\NotSupportedException;
use yii\db\ColumnSchemaBuilder;

/**
 * Class Migration
 * @package Smoren\Yii2\Microcore\base
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
    public function uuid()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid');
    }
}