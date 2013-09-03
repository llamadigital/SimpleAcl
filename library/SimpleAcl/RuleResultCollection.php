<?php
namespace SimpleAcl;

use SimpleAcl\RuleResult;
use SimpleAcl\SplPriorityQueue;
use IteratorAggregate;
use SimpleAcl\Strategy\AggregateStrategyFirstWins;

/**
 * Holds RuleResult sorted according priority.
 *
 */
class RuleResultCollection implements IteratorAggregate
{
    /**
     * @var SplPriorityQueue
     */
    public $collection;
    public $strategy = null;

    public function __construct()
    {
        $this->collection = new SplPriorityQueue();
        $this->strategy = new AggregateStrategyFirstWins();
    }

    public function getIterator()
    {
        return clone $this->collection;
    }

    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @param RuleResult $result
     */
    public function add(RuleResult $result = null)
    {
        if ( ! $result ) {
            return;
        }

        if ( is_null($result->getAction()) ) {
            return;
        }

        $this->collection->insert($result, $result->getPriority());
    }

    /**
     * @return bool
     */
    public function get()
    {
        if ( ! $this->any() ) {
            return false;
        }

        $result = $this->strategy->getResultAction($this->collection);

        return $result;
    }

    /**
     * @return bool
     */
    public function any()
    {
        return $this->collection->count() > 0;
    }
}
