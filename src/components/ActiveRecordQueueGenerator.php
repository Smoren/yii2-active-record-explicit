<?php


namespace Smoren\Yii2\ActiveRecordExplicit\components;


use Generator;
use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use Throwable;
use yii\db\StaleObjectException;

class ActiveRecordQueueGenerator extends ActiveRecordGenerator
{
    /**
     * @var int Page counter
     */
    protected $pageCounter = 0;

    /**
     * @return Generator
     * @throws DbException
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function &makeGenerator(): Generator
    {
        while(true) {
            if(!count($this->chunk)) {
                $this->query->limit($this->pageSize);
                $this->chunk = array_reverse($this->query->all($this->db));
                ($this->onPageGotHandler)($this->pageCounter++, $this->chunk);

                if(!count($this->chunk)) {
                    break;
                }
            }

            $item = array_pop($this->chunk);

            yield $item;

            $item->delete();
        }
    }
}