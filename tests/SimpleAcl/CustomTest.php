<?php
namespace SimpleAclTest;

use PHPUnit_Framework_TestCase;

use SimpleAcl\Acl;
use SimpleAcl\Role;
use SimpleAcl\Resource;
use SimpleAcl\Rule;
use SimpleAcl\RuleResult;
use SimpleAcl\Role\RoleAggregate;
use SimpleAcl\Resource\ResourceAggregate;
use SimpleAcl\Strategy\AggregateStrategyDenyWins;

class CustomTest extends PHPUnit_Framework_TestCase
{
  public function testAction()
  {
    $acl = new Acl();
    $admin = new Role('admin');

    $ProjectNews = new Resource('ProjectNews');
    $ProjectPage = new Resource('ProjectPage');
    $Messaging = new Resource('Messaging');
    $Users = new Resource('Users');
    $Segments = new Resource('Segments');
    $Points = new Resource('Points');
    $Projects = new Resource('Projects');
    $Tasks = new Resource('Tasks');
    $Reminders = new Resource('Reminders');
    $Accounts = new Resource('Accounts');
    $ProfileQuestions = new Resource('ProfileQuestions');
    $CMS = new Resource('CMS');
    $LandingPages = new Resource('LandingPages');

    $acl->addRule($admin, $ProjectNews, '*', true);
    $acl->addRule($admin, $ProjectPage, '*', true);
    $acl->addRule($admin, $Messaging, '*', true);
    $acl->addRule($admin, $Users, '*', true);
    $acl->addRule($admin, $Segments, '*', true);
    $acl->addRule($admin, $Points, '*', true);
    $acl->addRule($admin, $Projects, '*', true);
    $acl->addRule($admin, $Tasks, '*', true);        
    $acl->addRule($admin, $Reminders, '*', true);        
    $acl->addRule($admin, $Accounts, '*', true);        
    $acl->addRule($admin, $ProfileQuestions, '*', true);        
    $acl->addRule($admin, $CMS, 'access', true);        
    $acl->addRule($admin, $LandingPages, '*', true);        

    $enterprise = new Role('enterprise');

    $acl->addRule($enterprise, $ProjectNews, '*', true);
    $acl->addRule($enterprise, $ProjectPage, '*', true);
    $acl->addRule($enterprise, $Messaging, '*', true);
    $acl->addRule($enterprise, $Users, '*', true);
    $acl->addRule($enterprise, $Segments, '*', true);
    $acl->addRule($enterprise, $Points, '*', true);
    $acl->addRule($enterprise, $Projects, '*', true);
    $acl->addRule($enterprise, $Tasks, '*', true);        
    $acl->addRule($enterprise, $Reminders, '*', true);        
    $acl->addRule($enterprise, $Accounts, '*', true);        
    $acl->addRule($enterprise, $ProfileQuestions, '*', true);
    $acl->addRule($enterprise, $CMS, 'access', true);        
    $acl->addRule($enterprise, $LandingPages, '*', true);        

    $aggregate = new RoleAggregate();
    $strategy = new AggregateStrategyDenyWins();
    $aggregate->setStrategy($strategy);
    $aggregate->setRoles(array($admin, $enterprise));

    $i = 0;
    while($i < 1000) {
      $projects = $acl->isAllowed($aggregate, 'Projects', '*');
      $this->assertEquals(true, $projects);
      $i++;
    }
  }

}
