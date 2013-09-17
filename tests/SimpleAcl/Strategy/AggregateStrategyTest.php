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

class AggregateStrategyTest extends PHPUnit_Framework_TestCase
{
  public function testDenyWins()
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

    $acl->addRule($user, $siteFrontend, 'View', true);
    $acl->addRule($admin, $siteFrontend, 'View', true);

    $acl->addRule($user, $siteBackend, 'View', false);
    $acl->addRule($admin, $siteBackend, 'View', true);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'View'));
  }

  public function testAllDeny()
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

    $acl->addRule($user, $siteFrontend, 'View', false);
    $acl->addRule($admin, $siteFrontend, 'View', false);

    $acl->addRule($user, $siteBackend, 'View', false);
    $acl->addRule($admin, $siteBackend, 'View', false);

    $this->assertFalse($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'View'));
  }

  public function testAllAllow()
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

    $acl->addRule($user, $siteFrontend, 'View', true);
    $acl->addRule($admin, $siteFrontend, 'View', true);

    $acl->addRule($user, $siteBackend, 'View', true);
    $acl->addRule($admin, $siteBackend, 'View', true);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertTrue($acl->isAllowed($all, 'SiteBackend', 'View'));
  }

  public function testManyRoles()
  {
    $acl = new Acl();

    $role_one = new Role('one');
    $role_two = new Role('two');
    $role_three = new Role('three');
    $role_four = new Role('four');
    $role_five = new Role('five');

    $strategy = new AggregateStrategyDenyWins();

    $all = new RoleAggregate();
    $all->setStrategy($strategy);
    $all->addRole($role_one);
    $all->addRole($role_two);
    $all->addRole($role_three);
    $all->addRole($role_four);
    $all->addRole($role_five);

    $siteFrontend = new Resource('SiteFrontend');
    $siteBackend = new Resource('SiteBackend');
    $siteSecretSystem = new Resource('SiteSecretSystem');

    $acl->addRule($role_one, $siteFrontend, 'View', true);
    $acl->addRule($role_two, $siteFrontend, 'View', true);
    $acl->addRule($role_three, $siteFrontend, 'View', true);
    $acl->addRule($role_four, $siteFrontend, 'View', true);
    $acl->addRule($role_five, $siteFrontend, 'View', true);

    $acl->addRule($role_one, $siteBackend, 'View', true);
    $acl->addRule($role_two, $siteBackend, 'View', true);
    $acl->addRule($role_three, $siteBackend, 'View', false);
    $acl->addRule($role_four, $siteBackend, 'View', true);
    $acl->addRule($role_five, $siteBackend, 'View', true);

    $acl->addRule($role_one, $siteSecretSystem, 'View', false);
    $acl->addRule($role_two, $siteSecretSystem, 'View', true);
    $acl->addRule($role_three, $siteSecretSystem, 'View', false);
    $acl->addRule($role_four, $siteSecretSystem, 'View', false);
    $acl->addRule($role_five, $siteSecretSystem, 'View', false);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteSecretSystem', 'View'));
  }

  public function testNonExistantResource()
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

    $acl->addRule($user, $siteFrontend, 'View', true);
    $acl->addRule($admin, $siteFrontend, 'View', true);

    $acl->addRule($user, $siteBackend, 'View', false);
    $acl->addRule($admin, $siteBackend, 'View', true);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'NonExistant', 'View'));
  }

  public function testNonExistantPermission()
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

    $acl->addRule($user, $siteFrontend, 'View', true);
    $acl->addRule($admin, $siteFrontend, 'View', true);

    $acl->addRule($user, $siteBackend, 'View', false);
    $acl->addRule($admin, $siteBackend, 'View', true);

    $this->assertTrue($acl->isAllowed($all, 'SiteFrontend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'View'));
    $this->assertFalse($acl->isAllowed($all, 'SiteBackend', 'Hello'));
  }


  public function testPackageExample()
  {
    $acl = new Acl();

    $user = new Role('User');
    $admin = new Role('Admin');
    $bronze = new Role('Bronze');
    $gold = new Role('Gold');

    $strategy = new AggregateStrategyDenyWins();

    $gold_user = new RoleAggregate();
    $gold_user->setStrategy($strategy);
    $gold_user->addRole($user);
    $gold_user->addRole($gold);

    $gold_admin = new RoleAggregate();
    $gold_admin->setStrategy($strategy);
    $gold_admin->addRole($admin);
    $gold_admin->addRole($gold);

    $bronze_user = new RoleAggregate();
    $bronze_user->setStrategy($strategy);
    $bronze_user->addRole($user);
    $bronze_user->addRole($bronze);

    $bronze_admin = new RoleAggregate();
    $bronze_admin->setStrategy($strategy);
    $bronze_admin->addRole($admin);
    $bronze_admin->addRole($bronze);

    $articles = new Resource('Articles');

    $acl->addRule($user, $articles, 'edit', false);
    $acl->addRule($admin, $articles, 'edit', true);
    $acl->addRule($bronze, $articles, 'edit', false);
    $acl->addRule($gold, $articles, 'edit', true);

    $this->assertFalse($acl->isAllowed($gold_user, 'Articles', 'edit'));
    $this->assertTrue($acl->isAllowed($gold_admin, 'Articles', 'edit'));
    $this->assertFalse($acl->isAllowed($bronze_user, 'Articles', 'edit'));
    $this->assertFalse($acl->isAllowed($bronze_admin, 'Articles', 'edit'));
  }
}
