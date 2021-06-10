<?php


namespace Smoren\Yii2\ActiveRecordExplicit\models;

use Closure;
use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use Throwable;
use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;
use yii\db\Transaction;

/**
 * Расширенный класс для составления запроса к БД, связанный с Active Record
 * @see ActiveRecord
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    /**
     * Выбирает одну запись из полученного результата.
     * В случае, если записей несколько будет выбрашено исключение.
     * В случае, если записей нет, то будет выбрашено исключение.
     * @param null $db
     * @return array|void|\yii\db\ActiveRecord|null Экземпляр ActiveRecord модели
     * @throws DbException
     */
    public function one($db = null)
    {
        $rows = $this->limit(2)->all($db);
        $backtrace = debug_backtrace();

        if(count($backtrace) && strpos($backtrace[0]['file'], 'vendor/yiisoft/yii2/db/ActiveRelationTrait.php') !== false) {
            return $rows[0] ?? null;
        }

        $this->checkSingleResult($rows);

        return $rows[0];
    }

    /**
     * Выбирает первую запись из полученного результата.
     * В случае, если записей нет, то будет выбрашено исключение.
     * @param null $db
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
     * @return ActiveQuery
     */
    public function byId($id): self
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Получаем строки с блокировкой на запись и позволяет ими манипулировать с помощью замыкания
     * @param Closure $updater функция-замыкание для обновления полученных записей
     * @param null $db
     * @return ActiveRecord[] полученные строки
     * @throws Throwable
     */
    public function allForUpdate(Closure $updater, $db = null): array
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
     * @param Closure $updater
     * @param null $db
     * @return array|void|\yii\db\ActiveRecord|null Экземпляр ActiveRecord модели
     * @throws DbException
     * @throws Throwable
     */
    public function oneForUpdate(Closure $updater, $db = null)
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
        } catch(DbException | Throwable $e) {
            $transaction->rollBack();
            throw $e;
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
     * @param $condition
     * @param bool $filter
     * @param array $params
     * @return ActiveQuery
     */
    public function andWhereExtended($condition, bool $filter = false, array $params = [])
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
     * @return ActiveQuery
     */
    public function andHavingExtended($condition, bool $filter = false, array $params = [])
    {
        if($filter) {
            return $this->andFilterHaving($condition);
        } else {
            return $this->andHaving($condition, $params);
        }
    }
}