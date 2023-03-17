<?php

namespace Smoren\Yii2\ActiveRecordExplicit\components;

use Generator;
use Smoren\Yii2\ActiveRecordExplicit\models\ActiveQuery;
use Smoren\Yii2\ActiveRecordExplicit\models\ActiveRecord;
use yii\db\Connection;

class ActiveRecordGenerator
{
    /**
     * @var ActiveQuery
     */
    protected $query;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var ActiveRecord[]
     */
    protected $chunk;

    /**
     * @var callable
     */
    protected $onBeforeGetPage;

    /**
     * @var callable
     */
    protected $onAfterGetPage;

    /**
     * @var Connection|null
     */
    protected $db;

    /**
     * @param ActiveQuery $query
     * @param int $pageSize
     * @param callable|null $onBeforeGetPage
     * @param callable|null $onAfterGetPage
     * @param Connection|null $db
     * @return Generator
     */
    public static function generate(
        ActiveQuery $query,
        int $pageSize,
        ?callable $onBeforeGetPage = null,
        ?callable $onAfterGetPage = null,
        ?Connection $db = null
    ): Generator {
        $inst = new static($query, $pageSize, $onBeforeGetPage, $onAfterGetPage, $db);
        return $inst->generator;
    }

    /**
     * ActiveRecordGenerator constructor.
     * @param ActiveQuery $query
     * @param int $pageSize
     * @param callable|null $onBeforeGetPage
     * @param callable|null $onAfterGetPage
     * @param Connection|null $db
     */
    protected function __construct(
        ActiveQuery $query,
        int $pageSize,
        ?callable $onBeforeGetPage = null,
        ?callable $onAfterGetPage = null,
        ?Connection $db = null
    )
    {
        $this->db = $db;
        $this->query = $query;
        $this->pageSize = $pageSize;
        $this->page = 0;
        $this->chunk = [];

        if($onBeforeGetPage === null) {
            $this->onBeforeGetPage = function(int $page, array &$chunk) {};
        } else {
            $this->onBeforeGetPage = $onBeforeGetPage;
        }

        if($onAfterGetPage === null) {
            $this->onAfterGetPage = function(int $page, array &$chunk) {};
        } else {
            $this->onAfterGetPage = $onAfterGetPage;
        }

        $this->generator = $this->makeGenerator();
    }

    /**
     * @return Generator
     */
    protected function &makeGenerator(): Generator
    {
        while(true) {
            if(!count($this->chunk)) {
                ($this->onBeforeGetPage)($this->page, $this->chunk);
                $this->chunk = array_reverse(
                    $this->query->offset($this->page*$this->pageSize)->limit($this->pageSize)->all($this->db)
                );
                ($this->onAfterGetPage)($this->page, $this->chunk);
                $this->page++;

                if(!count($this->chunk)) {
                    break;
                }
            }

            yield $this->chunk[count($this->chunk)-1];
            array_pop($this->chunk);
        }
    }
}
