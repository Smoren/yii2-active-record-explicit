<?php

namespace Smoren\Yii2\ActiveRecordExplicit\components;

use yii\base\InvalidConfigException;
use yii\db\QueryInterface;

/**
 * Расширенный постащик данных для AR моделей
 */
class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    /**
     * @var callable Функция обратного вызова для обработки одной модели
     */
    public $modelCallback;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    protected function prepareModels(): array
    {
        if(!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        if(($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if($pagination->totalCount === 0) {
                return [];
            }
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        if(($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        $result = [];
        $data = $query->all($this->db);

        if($this->modelCallback && is_callable($this->modelCallback)) {
            foreach($data as $model) {
                $result[] = call_user_func($this->modelCallback, $model);
            }
        } else {
            $result = $data;
        }

        return $result;
    }
}
