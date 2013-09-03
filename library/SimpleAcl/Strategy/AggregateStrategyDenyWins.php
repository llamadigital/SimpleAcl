<?php
namespace SimpleAcl\Strategy;

class AggregateStrategyDenyWins extends AggregateStrategy
{
  public function getResultAction($rule_result_collection)
  {
    $action = true;

    foreach($rule_result_collection as $rule_result) {
      $action = $rule_result->getAction();

      //any falsey value is assumed to be deny
      if($action == false) {
        break;
      }
    }

    return $action;
  }
}
