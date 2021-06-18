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
    protected $onPageGotHandler;

    /**
     * @var Connection|null
     */
    protected $db;

    /**
     * @param ActiveQuery $query
     * @param int $pageSize
     * @param callable|null $onPageGotHandler
     * @param Connection|null $db
     * @return Generator
     */
    public static function generate(
        ActiveQuery $query, int $pageSize, ?callable $onPageGotHandler = null, ?Connection $db = null
    ): Generator
    {
        $inst = new static($query, $pageSize, $onPageGotHandler, $db);
        return $inst->generator;
    }

    /**
     * ActiveRecordGenerator constructor.
     * @param ActiveQuery $query
     * @param int $pageSize
     * @param callable|null $onPageGotHandler
     * @param Connection|null $db
     */
    protected function __construct(
        ActiveQuery $query, int $pageSize, ?callable $onPageGotHandler = null, ?Connection $db = null
    )
    {
        $this->db = $db;
        $this->query = $query;
        $this->pageSize = $pageSize;
        $this->page = 0;
        $this->chunk = [];

        if($onPageGotHandler === null) {
            $this->onPageGotHandler = function(int $page, array &$chunk) {};
        } else {
            $this->onPageGotHandler = $onPageGotHandler;
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
                $this->chunk = array_reverse(
                    $this->query->offset($this->page*$this->pageSize)->limit($this->pageSize)->all($this->db)
                );
                ($this->onPageGotHandler)($this->page, $this->chunk);
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
