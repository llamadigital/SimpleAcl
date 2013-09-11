<?php
namespace SimpleAclTest;

use PHPUnit_Framework_TestCase;

use SimpleAcl\Acl;
use SimpleAcl\Role;
use SimpleAcl\Role\RoleAggregate;
use SimpleAcl\Resource;
use SimpleAcl\Strategy\AggregateStrategyDenyWins;
use SimpleAcl\Object;
use SimpleAcl\Object\RecursiveIterator;
use RecursiveIteratorIterator;

class MatchAnyTest extends PHPUnit_Framework_TestCase
{
  public function testAny()
  {
    $acl = new Acl();
    $user = new Role('User');
    $siteFrontend = new Resource('SiteFrontend');
    $acl->addRule($user, $siteFrontend, '*', true);
    $this->assertTrue($acl->isAllowed($user, 'SiteFrontend', 'Edit'));
  }
}
