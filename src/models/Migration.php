<?php

namespace Smoren\Yii2\ActiveRecordExplicit\models;

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
    public function uuidPrimaryKey(): ColumnSchemaBuilder
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid NOT NULL PRIMARY KEY DEFAULT uuid_generate_v4()');
    }

    /**
     * @return ColumnSchemaBuilder
     * @throws NotSupportedException
     */
    public function uuidAutoGen(): ColumnSchemaBuilder
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid NOT NULL DEFAULT uuid_generate_v4()');
    }

    /**
     * @return ColumnSchemaBuilder
     * @throws NotSupportedException
     */
    public function uuid(): ColumnSchemaBuilder
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid');
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function alias(bool $unique = true): ColumnSchemaBuilder
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
    public function longAlias(bool $unique = true): ColumnSchemaBuilder
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
    public function name(): ColumnSchemaBuilder
    {
        return $this->string(255)->notNull();
    }

    /**
     * @return ColumnSchemaBuilder
     * @throws NotSupportedException
     */
    public function createdAt(bool $withMilliseconds = false): ColumnSchemaBuilder
    {
        switch($this->db->driverName) {
            case 'mysql':
                return $this->bigInteger()->null();
            case 'pgsql':
                if($withMilliseconds) {
                    $expr = '(extract(epoch from now())*1000)::BIGINT';
                } else {
                    $expr = '(extract(epoch from now()))::BIGINT';
                }
                return $this->bigInteger()->notNull()->defaultValue(new Expression($expr));
            default:
                throw new NotSupportedException("unknown driver {$this->db->driverName}", 1);
        }
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function updatedAt(): ColumnSchemaBuilder
    {
         return $this->bigInteger();
    }

    /**
     * @param string|null $name
     * @inheritDoc
     */
    public function createIndex($name, $table, $columns, $unique = false): void
    {
        if($name === null) {
            $name = 'idx-'
                .$table
                .'-'
                .(is_array($columns) ? implode('-', $columns) : $columns);
        }

        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * @param string|null $name
     * @inheritDoc
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null): void
    {
        if($name === null) {
            $name = 'fk-'
                .$table
                .'-'
                .(is_array($columns) ? implode('-', $columns) : $columns)
                .'-'
                .$refTable
                .'-'
                .(is_array($refColumns) ? implode('-', $refColumns) : $refColumns);
        }

        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @param string|null $name
     * @inheritDoc
     */
    public function addPrimaryKey($name, $table, $columns): void
    {
        if($name === null) {
            $name = 'pk-'
                .$table
                .'-'
                .(is_array($columns) ? implode('-', $columns) : $columns);
        }

        parent::addPrimaryKey($name, $table, $columns);
    }
}
