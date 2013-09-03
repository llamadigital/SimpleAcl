<?php
namespace SimpleAcl\Strategy;

class AggregateStrategyFirstWins extends AggregateStrategy
{
  public function getResultAction($rule_result_collection)
  {
    return $rule_result_collection->top()->getAction();
  }
}
