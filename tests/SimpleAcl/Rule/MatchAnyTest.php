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
    $this->assertTrue($acl->isAllowed('User', 'SiteFrontend', 'Edit'));
  }

  public function testDifferentResource()
  {
    $acl = new Acl();
    $user = new Role('User');
    $siteFrontend = new Resource('SiteFrontend');
    $siteBackend = new Resource('SiteBackend');
    $acl->addRule($user, $siteFrontend, '*', true);
    $this->assertFalse($acl->isAllowed('User', 'SiteBackend', 'Edit'));
  }

  public function testAggregate()
  {
    $acl = new Acl();

    $user = new Role('User');
    $admin = new Role('Admin');

    $all = new RoleAggregate();
    $all->addRole($user);
    $all->addRole($admin);

    $siteFrontend = new Resource('SiteFrontend');
    $siteBackend = new Resource('SiteBackend');

    $acl->addRule($user, $siteFrontend, '*', true);
    $acl->addRule($admin, $siteFrontend, '*', true);

    $acl->addRule($admin, $siteBackend, '*', true);
    $acl->addRule($user, $siteBackend, '*', false);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertTrue($acl->isAllowed($all, 'SiteBackend', 'View'));
  }

  public function testAggregateDenyWins()
  {
    $acl = new Acl();

    $user = new Role('User');
    $admin = new Role('Admin');

    $strategy = new AggregateStrategyDenyWins();

    $all = new RoleAggregate();
    $all->setStrategy($strategy);
    $all->addRole($user);
    $all->addRole($admin);

    $siteFrontend = new Resource('SiteFrontend');
    $siteBackend = new Resource('SiteBackend');

    $acl->addRule($user, $siteFrontend, '*', true);
    $acl->addRule($admin, $siteFrontend, '*', true);

    $acl->addRule($admin, $siteBackend, '*', true);
    $acl->addRule($user, $siteBackend, '*', false);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'View'));
  }

  public function testRuleNotDefinedAggregate()
  {
    $acl = new Acl();

    $user = new Role('User');
    $admin = new Role('Admin');

    $strategy = new AggregateStrategyDenyWins();

    $all = new RoleAggregate();
    $all->setStrategy($strategy);
    $all->addRole($user);
    $all->addRole($admin);

    $siteFrontend = new Resource('SiteFrontend');
    $siteBackend = new Resource('SiteBackend');

    $acl->addRule($user, $siteFrontend, '*', true);

    $acl->addRule($admin, $siteBackend, '*', true);
    $acl->addRule($user, $siteBackend, '*', false);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'View'));
  }
}
