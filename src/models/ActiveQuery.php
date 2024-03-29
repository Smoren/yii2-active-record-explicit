<?php

namespace Smoren\Yii2\ActiveRecordExplicit\models;

use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;
use yii\db\Connection;
use yii\db\Transaction;
use Yii;
use Throwable;

/**
 * Расширенный класс для составления запроса к БД, связанный с Active Record
 * @see ActiveRecord
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    /**
     * @var ActiveRecord::class
     */
    public $modelClass;

    /**
     * @var Connection|null
     */
    protected $connection = null;

    /**
     * @param $modelClass
     * @param array $config
     */
    public function __construct($modelClass, array $config = [])
    {
        parent::__construct($modelClass, $config);
        $this->connection = $modelClass::getDb();
    }

    /**
     * @inheritDoc
     */
    public function all($db = null)
    {
        return parent::all($this->getConnection($db));
    }

    /**
     * Выбирает одну запись из полученного результата.
     * В случае, если записей несколько будет выброшено исключение.
     * В случае, если записей нет, то будет выброшено исключение.
     * @param Connection|null $db
     * @return array|ActiveRecord Экземпляр ActiveRecord модели
     * @throws DbException
     */
    public function one($db = null)
    {
        $rows = $this->limit(2)->all($db);
        $backtrace = debug_backtrace();

        if(count($backtrace)) {
            foreach($backtrace as $step) {
                if(isset($step['file']) && strpos($step['file'], 'vendor/yiisoft/yii2/db/ActiveRelationTrait.php') !== false) {
                    return $rows[0] ?? null;
                }
            }
        }

        $this->checkSingleResult($rows);

        return $rows[0];
    }

    /**
     * Выбирает первую запись из полученного результата.
     * В случае, если записей нет, то будет выброшено исключение.
     * @param Connection|null $db
     * @return ActiveRecord|array Экземпляр ActiveRecord модели
     * @throws DbException
     */
    public function first($db = null)
    {
        $rows = $this->limit(1)->all($db);
        if(!sizeof($rows)) {
            throw new DbException('no rows found for one', DbException::STATUS_EMPTY_RESULT);
        }

        return $rows[0];
    }

    /**
     * Фильтрует записи по ID
     * @param $id
     * @return static
     */
    public function byId($id): self
    {
        return $this->andWhere([$this->aliasColumn('id') => $id]);
    }

    /**
     * Подставляет алиас таблицы к названию колонки
     * @param string $columnName
     * @return string
     */
    public function aliasColumn(string $columnName): string
    {
        [$tableName, $alias] = $this->getTableNameAndAlias();
        return "{$alias}.{$columnName}";
    }

    /**
     * Получаем строки с блокировкой на запись и позволяет ими манипулировать с помощью замыкания
     * @param callable $updater функция-замыкание для обновления полученных записей
     * @param Connection|null $db
     * @return ActiveRecord[] полученные строки
     * @throws Throwable
     */
    public function allForUpdate(callable $updater, ?Connection $db = null): array
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::READ_COMMITTED);

        try {
            $sql = $this->createCommand()->getRawSql();
            $items = $this->modelClass::findBySql("{$sql} FOR UPDATE")->all($db);
            $updater($items);
            $transaction->commit();
        } catch(Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $items;
    }

    /**
     * Выбирает одну запись из полученного результата.
     * В случае, если записей несколько будет выбрашено исключение.
     * В случае, если записей нет, то будет выбрашено исключение.
     * @param callable $updater
     * @param Connection|null $db
     * @return array|ActiveRecord Экземпляр ActiveRecord модели
     * @throws DbException
     */
    public function oneForUpdate(callable $updater, ?Connection $db = null)
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::READ_COMMITTED);

        try {
            $this->limit(2);
            $sql = $this->createCommand()->getRawSql();
            $items = $this->modelClass::findBySql("{$sql} FOR UPDATE")->all($db);

            $this->checkSingleResult($items);

            $updater($items[0]);
            $transaction->commit();

            return $items[0];
        } catch(DbException $e) {
            $transaction->rollBack();
            throw $e;
        } catch(Throwable $e) {
            $transaction->rollBack();
            throw new DbException('oneForUpdate error', DbException::STATUS_UNKNOWN, $e);
        }
    }

    /**
     * @param array $result
     * @return $this
     * @throws DbException
     */
    protected function checkSingleResult(array &$result): self
    {
        if(!sizeof($result)) {
            throw new DbException("no rows found for one for {$this->modelClass}", DbException::STATUS_EMPTY_RESULT);
        }
        if(sizeof($result) > 1) {
            throw new DbException('multiple rows found for one', DbException::STATUS_REDUDANT_RESULT);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param ActiveRecordInterface|BaseActiveRecord $model
     * @return mixed|null
     */
    public function findFor($name, $model)
    {
        try {
            return parent::findFor($name, $model);
        } catch(DbException $e) {
            return null;
        }
    }

    /**
     * @param string $columnName
     * @param mixed $columnValue
     * @param bool $filter
     * @param array $params
     * @return $this
     */
    public function addColumnWhereCondition(
        string $columnName,
        $columnValue,
        bool $filter = false,
        array $params = []
    ): self
    {
        return $this->andWhereExtended([$this->aliasColumn($columnName) => $columnValue], $filter, $params);
    }

    /**
     * @param string $operator
     * @param string $columnName
     * @param mixed $columnValue
     * @param bool $filter
     * @param array $params
     * @return $this
     */
    public function addColumnOperatorCondition(
        string $operator,
        string $columnName,
        $columnValue,
        bool $filter = false,
        array $params = []
    ): self
    {
        if($filter && $columnValue === null) {
            return $this;
        }

        if(is_array($columnValue)) {
            $arguments = [$operator, $columnName];
            foreach($columnValue as $value) {
                $arguments[] = $value;
            }
            return $this->andWhere([$operator, $columnName, $arguments], $params);
        }

        return $this->andWhere([$operator, $columnName, $columnValue], $params);
    }

    /**
     * @param $condition
     * @param bool $filter
     * @param array $params
     * @return static
     */
    public function andWhereExtended($condition, bool $filter = false, array $params = []): self
    {
        if($filter) {
            return $this->andFilterWhere($condition);
        } else {
            return $this->andWhere($condition, $params);
        }
    }

    /**
     * @param $condition
     * @param bool $filter
     * @param array $params
     * @return static
     */
    public function andHavingExtended($condition, bool $filter = false, array $params = []): self
    {
        if($filter) {
            return $this->andFilterHaving($condition);
        } else {
            return $this->andHaving($condition, $params);
        }
    }

    /**
     * @param Connection|null $connection
     * @return Connection
     */
    protected function getConnection(?Connection $connection): Connection
    {
        if($connection !== null) {
            return $connection;
        }

        if($this->connection !== null) {
            return $this->connection;
        }

        return Yii::$app->getDb();
    }
}
