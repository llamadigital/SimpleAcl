<?php
namespace SimpleAcl;

use SimpleAcl\Role;
use SimpleAcl\Resource;
use SimpleAcl\Rule;

use SimpleAcl\Role\RoleAggregateInterface;
use SimpleAcl\Resource\ResourceAggregateInterface;
use SimpleAcl\Exception\InvalidArgumentException;
use SimpleAcl\Exception\RuntimeException;
use SimpleAcl\RuleResultCollection;

/**
 * Access Control List (ACL) management.
 *
 */
class Acl
{
    /**
     * Contains registered rules.
     *
     * @var Rule[]
     */
    protected $rules = array();

    /**
     * Class name used when rule created from string.
     *
     * @var string
     */
    protected $ruleClass = 'SimpleAcl\Rule';


    /**
     * Original design iterated over all rules for every request, this
     * provides a quick hash cache to specific rules
     */
    protected $rules_cache = null;

    /**
     * Set rule class.
     *
     * @param string $ruleClass
     */
    public function setRuleClass($ruleClass)
    {
        if ( ! class_exists($ruleClass) ) {
            throw new RuntimeException('Rule class not exist');
        }

        if ( ! is_subclass_of($ruleClass, 'SimpleAcl\Rule') && $ruleClass != 'SimpleAcl\Rule' ) {
            throw new RuntimeException('Rule class must be instance of SimpleAcl\Rule');
        }

        $this->ruleClass = $ruleClass;
    }

    /**
     * Return rule class.
     *
     * @return string
     */
    public function getRuleClass()
    {
        return $this->ruleClass;
    }

    private function ensureRulesCache()
    {
      if($this->rules_cache == null) {
        $this->updateRulesCache();
      }
    }

    private function updateRulesCache()
    {
      $this->rules_cache = array();

      foreach($this->rules as $rule) {
        $this->addRuleToCache($rule);
      }
    }

    private function addRuleToCache($rule)
    {
      $hash_name = $this->getRuleHash($rule);

      $this->AddRuleToCacheAt($rule, $hash_name);
    }

    private function addRuleToCacheAt($rule, $hash_name)
    {
      if(!array_key_exists($hash_name, $this->rules_cache)) {
        $this->rules_cache[$hash_name] = array();
      }
      $this->rules_cache[$hash_name][] = $rule;
    }

    /* private function updateChildrenCache($rule) */
    /* { */
    /*   $this->updateChildrenRoles($rule); */
    /*   $this->updateChildrenResources($rule); */
    /* } */

    /* private function updateChildrenRoles($rule) */
    /* { */
    /*   $role = $rule->getRole(); */
    /*   if(isset($role)) { */
    /*     $child_roles = $role->getChildren(); */

    /*     foreach($child_roles as $child_role) { */
    /*       $rule_name = $rule->getName(); */
    /*       $resource_name = $rule->getResourceName(); */
    /*       $role_name = $child_role->getName(); */

    /*       $hash_name = */ 
    /*         $this->getRuleHashFromNames($rule_name, $resource_name, $role_name); */
    /*       $this->addRuleToCacheAt($rule, $hash_name); */
    /*     } */
    /*   } */
    /* } */

    /* private function updateChildrenResources($rule) */
    /* { */
    /*   $resource = $rule->getResource(); */
    /*   if(isset($resource)) { */
    /*     $child_resources = $resource->getChildren(); */

    /*     foreach($child_resources as $child_resource) { */
    /*       $rule_name = $rule->getName(); */
    /*       $resource_name = $child_resource->getName(); */
    /*       $role_name = $rule->getRoleName(); */

    /*       $hash_name = */ 
    /*         $this->getRuleHashFromNames($rule_name, $resource_name, $role_name); */
    /*       $this->addRuleToCacheAt($rule, $hash_name); */
    /*     } */
    /*   } */
    /* } */

    private function getRuleHash($rule)
    {
      $rule_name = $rule->getName();
      $resource_name = $rule->getResourceName();
      $role_name = $rule->getRoleName();

      return $this->getRuleHashFromNames($rule_name, $resource_name, $role_name);
    }

    private function getRuleHashFromNames($rule_name, $resource_name, $role_name)
    {
      return $rule_name . $resource_name . $role_name;
    }

    /**
     * Return true if rule was already added.
     *
     * @param Rule | mixed $needRule Rule or rule's id
     * @return bool
     */
    public function hasRule($needRule)
    {
        foreach ( $this->rules as $rule ) {
            $needRuleId = ($needRule instanceof Rule) ? $needRule->getId() : $needRule;
            if ( $rule->getId() == $needRuleId ) {
                return $rule;
            }
        }

        return false;
    }

    /**
     * Adds rule.
     *
     * Assign $role, $resource and $action to added rule.
     * If rule was already registered only change $role, $resource and $action for that rule.
     *
     * This method accept 1, 2, 3 or 4 arguments:
     *
     * addRule($rule)
     * addRule($rule, $action)
     * addRule($role, $resource, $rule)
     * addRule($role, $resource, $rule, $action)
     *
     * @param Role $role
     * @param Resource $resource
     * @param Rule|string $rule
     * @param mixed $action
     *
     * @throws InvalidArgumentException
     */
    public function addRule()
    {
	    $args = func_get_args();
	    $argsCount = count($args);

	    $role = null;
	    $resource = null;
	    $action = null;

	    if ( $argsCount == 4 || $argsCount == 3 ) {
		    $role = $args[0];
		    $resource = $args[1];
		    $rule = $args[2];
		    if ( $argsCount == 4) {
		        $action = $args[3];
		    }
	    } elseif( $argsCount == 2 ) {
		    $rule = $args[0];
		    $action = $args[1];
	    } elseif ( $argsCount == 1 ) {
		    $rule = $args[0];
	    } else {
		    throw new InvalidArgumentException(__METHOD__ . ' accepts only one, tow, three or four arguments');
	    }

	    if ( ! is_null($role) && ! $role instanceof Role ) {
		    throw new InvalidArgumentException('Role must be an instance of SimpleAcl\Role or null');
	    }

	    if ( ! is_null($resource) && ! $resource instanceof Resource ) {
		    throw new InvalidArgumentException('Resource must be an instance of SimpleAcl\Resource or null');
	    }

        if ( is_string($rule) ) {
            $ruleClass = $this->getRuleClass();
            $rule = new $ruleClass($rule);
        }

        if ( ! $rule instanceof Rule ) {
            throw new InvalidArgumentException('Rule must be an instance of SimpleAcl\Rule or string');
        }

        if ( $exchange = $this->hasRule($rule) ) {
            $rule = $exchange;
        }

        if ( ! $exchange ) {
            $this->rules[] = $rule;
        }

	    if ( $argsCount == 3 || $argsCount == 4 ) {
            $rule->setRole($role);
            $rule->setResource($resource);
	    }

	    if ( $argsCount == 2 || $argsCount == 4 ) {
            $rule->setAction($action);
	    }
    }

    /**
     * Get names.
     *
     * @param string|RoleAggregateInterface|ResourceAggregateInterface $object
     * @return array
     */
    protected function getNames($object)
    {
        if ( is_string($object) ) {
            return array($object);
        } elseif ( $object instanceof RoleAggregateInterface ) {
            return $object->getRolesNames();
        } elseif ( $object instanceof ResourceAggregateInterface ) {
            return $object->getResourcesNames();
        }

        return array();
    }

    private function getMatchingRulesFromCache($rule_name, $resource_name, 
      $role_name)
    {
      $rules = array();
      $hash = $this->getRuleHashFromNames($rule_name, $resource_name, $role_name);

      if(array_key_exists($hash, $this->rules_cache)) {
        $rules = array_merge($rules, $this->rules_cache[$hash]);
      }

      return $rules;
    }

    /**
     * Check is access allowed by some rule.
     * Returns null if rule don't match any role or resource.
     *
     * @param string $roleName
     * @param string $resourceName
     * @param $ruleName
     * @param RuleResultCollection $ruleResultCollection
     * @param string|RoleAggregateInterface $roleAggregate
     * @param string|ResourceAggregateInterface $resourceAggregate
     */
    protected function isRuleAllow($roleName, $resourceName, $ruleName, 
      RuleResultCollection $ruleResultCollection, $roleAggregate, $resourceAggregate)
    {
        $this->ensureRulesCache();
        $rules = $this->getMatchingRulesFromCache($ruleName, $resourceName,
          $roleName);

        foreach ($rules as $rule) {
            $rule->resetAggregate($roleAggregate, $resourceAggregate);

            $result = $rule->isAllowed($ruleName, $roleName, $resourceName);
            $ruleResultCollection->add($result);
        }

    }

    protected function addAnyRuleResultToResultSet($roleName, $resourceName,
      $ruleResultCollection)
    {
      $any_resultSet = $this->matchAnyRuleResult($roleName, $resourceName);
      $ruleResultCollection->merge($any_resultSet);

      return $ruleResultCollection;
    }

    protected function matchAnyRuleResult($roleName, $resourceName)
    {
      $result_set = $this->isAllowedReturnResult($roleName, 
        $resourceName, "*");

      return $result_set;
    }

    /**
     * Checks is access allowed.
     *
     * @param string|RoleAggregateInterface $roleName
     * @param string|ResourceAggregateInterface $resourceName
     * @param string $ruleName
     * @return bool
     */
    public function isAllowed($roleName, $resourceName, $ruleName)
    {
        $result_set = $this->isAllowedReturnResult($roleName, $resourceName, $ruleName);
        return $result_set->get();
    }

    /**
     * Checks is access allowed.
     *
     * @param string|RoleAggregateInterface $roleAggregate
     * @param string|ResourceAggregateInterface $resourceAggregate
     * @param string $ruleName
     *
     * @return RuleResultCollection
     */
    public function isAllowedReturnResult($roleAggregate, $resourceAggregate, $ruleName)
    {
      $ruleResultCollection = $this->getRuleResultCollection($roleAggregate);

      $roles = $this->getNames($roleAggregate);
      $resources = $this->getNames($resourceAggregate);

      foreach ($roles as $roleName) {
        foreach ($resources as $resourceName) {
          $this->isRuleAllow($roleName, $resourceName, $ruleName, 
            $ruleResultCollection, $roleAggregate, $resourceAggregate);

          if($ruleName != "*") {
            $ruleResultCollection = $this->addAnyRuleResultToResultSet(
              $roleName, $resourceName, $ruleResultCollection);
          }

        }
      }

      return $ruleResultCollection;
    }

    private function getRuleResultCollection($roleAggregate)
    {
        $ruleResultCollection = new RuleResultCollection();

        if ( $roleAggregate instanceof RoleAggregateInterface ) {
            $ruleResultCollection = $roleAggregate->newRuleResultCollection();
        }

        return $ruleResultCollection;
    }

    /**
     * Remove all rules.
     *
     */
    public function removeAllRules()
    {
        $this->rules = array();
    }

    /**
     * Remove rules by rule name and (or) role and resource.
     *
     * @param null|string $roleName
     * @param null|string $resourceName
     * @param null|string $ruleName
     * @param bool $all
     */
    public function removeRule($roleName = null, $resourceName = null, $ruleName = null, $all = true)
    {
        if ( is_null($roleName) && is_null($resourceName) && is_null($ruleName) ) {
            $this->removeAllRules();
            return;
        }

        foreach ( $this->rules as $ruleIndex => $rule ) {
            if ( $ruleName === null || ($ruleName !== null && $ruleName == $rule->getName()) ) {
                if ( $roleName === null || ($roleName !== null && $rule->getRole() && $rule->getRole()->getName() == $roleName) ) {
                    if ( $resourceName === null || ($resourceName !== null && $rule->getResource() && $rule->getResource()->getName() == $resourceName) ) {
                        unset($this->rules[$ruleIndex]);
                        if ( ! $all ) {
                            return;
                        }
                    }
                }
            }
        }
    }

    /**
     * Removes rule by its id.
     *
     * @param mixed $ruleId
     */
    public function removeRuleById($ruleId)
    {
        foreach ($this->rules as $ruleIndex => $rule) {
            if ( $rule->getId() == $ruleId ) {
                unset($this->rules[$ruleIndex]);
                return;
            }
        }
    }
}
