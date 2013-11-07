<?php
namespace SimpleAclTest;

use PHPUnit_Framework_TestCase;

use SimpleAcl\Acl;
use SimpleAcl\Role;
use SimpleAcl\Resource;
use SimpleAcl\Rule;
use SimpleAcl\Role\RoleAggregate;
use SimpleAcl\Resource\ResourceAggregate;
use SimpleAcl\RuleResult;

class AclRuleApplyTest extends PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $acl = new Acl;

        $this->assertFalse($acl->isAllowed('User', 'Page', 'View'));
    }

    public function testUnDefinedRule()
    {
        $acl = new Acl;
        $acl->addRule(new Role('User'), new Resource('Page'), new Rule('View'), true);

        $this->assertFalse($acl->isAllowed('User', 'Page', 'UnDefinedRule'));
    }

    public function testUnDefinedRoleOrResource()
    {
        $acl = new Acl;
        $acl->addRule(new Role('User'), new Resource('Page'), new Rule('View'), true);

        $this->assertFalse($acl->isAllowed('NotDefinedRole', 'Page', 'View'));
        $this->assertFalse($acl->isAllowed('User', 'NotDefinedResource', 'View'));
        $this->assertFalse($acl->isAllowed('NotDefinedRole', 'NotDefinedResource', 'View'));
    }

    public function testOneRoleOneResourceOneRule()
    {
        $acl = new Acl;
        $acl->addRule(new Role('User'), new Resource('Page'), new Rule('View'), true);
        $this->assertTrue($acl->isAllowed('User', 'Page', 'View'));

        $acl = new Acl;
        $acl->addRule(new Role('User'), new Resource('Page'), new Rule('View'), false);
        $this->assertFalse($acl->isAllowed('User', 'Page', 'View'));
    }

    public function testOneRoleOneResourceMultipleRule()
    {
        $acl = new Acl;

        $user = new Role('User');
        $resource = new Resource('Page');

        $acl->addRule($user, $resource, new Rule('View'), true);
        $acl->addRule($user, $resource, new Rule('Edit'), true);
        $acl->addRule($user, $resource, new Rule('Remove'), true);

        $this->assertTrue($acl->isAllowed('User', 'Page', 'View'));
        $this->assertTrue($acl->isAllowed('User', 'Page', 'Edit'));
        $this->assertTrue($acl->isAllowed('User', 'Page', 'Remove'));

        $acl = new Acl;

        $user = new Role('User');
        $resource = new Resource('Page');

        $acl->addRule($user, $resource, new Rule('View'), false);
        $acl->addRule($user, $resource, new Rule('Edit'), false);
        $acl->addRule($user, $resource, new Rule('Remove'), false);

        $this->assertFalse($acl->isAllowed('User', 'Page', 'View'));
        $this->assertFalse($acl->isAllowed('User', 'Page', 'Edit'));
        $this->assertFalse($acl->isAllowed('User', 'Page', 'Remove'));
    }

    public function testMultipleRolesMultipleResourcesMultipleRules()
    {
        $runChecks = function(PHPUnit_Framework_TestCase $phpUnit, Acl $acl, $allowed) {
            // Checks for page
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Page', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Page', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Page', 'Remove'));

            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Page', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Page', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Page', 'Remove'));

            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Page', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Page', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Page', 'Remove'));
    
            // Checks for blog
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Blog', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Blog', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Blog', 'Remove'));
    
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Blog', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Blog', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Blog', 'Remove'));
    
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Blog', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Blog', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Blog', 'Remove'));
    
            // Checks for site
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Site', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Site', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('User', 'Site', 'Remove'));

            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Site', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Site', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Moderator', 'Site', 'Remove'));

            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Site', 'View'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Site', 'Edit'));
            $phpUnit->assertEquals($allowed, $acl->isAllowed('Admin', 'Site', 'Remove'));
        };
        
        $acl = new Acl;

        $user = new Role('User');
        $moderator = new Role('Moderator');
        $admin = new Role('Admin');

        $page = new Resource('Page');
        $blog = new Resource('Blog');
        $site = new Resource('Site');

        $runChecks($this, $acl, false);

        // Rules for page
        $acl->addRule($user, $page, new Rule('View'), true);
        $acl->addRule($user, $page, new Rule('Edit'), true);
        $acl->addRule($user, $page, new Rule('Remove'), true);

        $acl->addRule($moderator, $page, new Rule('View'), true);
        $acl->addRule($moderator, $page, new Rule('Edit'), true);
        $acl->addRule($moderator, $page, new Rule('Remove'), true);

        $acl->addRule($admin, $page, new Rule('View'), true);
        $acl->addRule($admin, $page, new Rule('Edit'), true);
        $acl->addRule($admin, $page, new Rule('Remove'), true);

        // Rules for blog
        $acl->addRule($user, $blog, new Rule('View'), true);
        $acl->addRule($user, $blog, new Rule('Edit'), true);
        $acl->addRule($user, $blog, new Rule('Remove'), true);

        $acl->addRule($moderator, $blog, new Rule('View'), true);
        $acl->addRule($moderator, $blog, new Rule('Edit'), true);
        $acl->addRule($moderator, $blog, new Rule('Remove'), true);

        $acl->addRule($admin, $blog, new Rule('View'), true);
        $acl->addRule($admin, $blog, new Rule('Edit'), true);
        $acl->addRule($admin, $blog, new Rule('Remove'), true);

        // Rules for site
        $acl->addRule($user, $site, new Rule('View'), true);
        $acl->addRule($user, $site, new Rule('Edit'), true);
        $acl->addRule($user, $site, new Rule('Remove'), true);

        $acl->addRule($moderator, $site, new Rule('View'), true);
        $acl->addRule($moderator, $site, new Rule('Edit'), true);
        $acl->addRule($moderator, $site, new Rule('Remove'), true);

        $acl->addRule($admin, $site, new Rule('View'), true);
        $acl->addRule($admin, $site, new Rule('Edit'), true);
        $acl->addRule($admin, $site, new Rule('Remove'), true);

        $runChecks($this, $acl, true);

    }

    public function testAggregateEmptyRolesAndResources()
    {
        $acl = new Acl;

       $user = new Role('User');
       $moderator = new Role('Moderator');
       $admin = new Role('Admin');

       $page = new Resource('Page');
       $blog = new Resource('Blog');
       $site = new Resource('Site');

       $userGroup = new RoleAggregate();
       $siteGroup = new ResourceAggregate();

       $acl->addRule($user, $page, new Rule('View'), true);
       $acl->addRule($moderator, $blog, new Rule('Edit'), true);
       $acl->addRule($admin, $site, new Rule('Remove'), true);

       $this->assertFalse($acl->isAllowed($userGroup, $siteGroup, 'View'));
       $this->assertFalse($acl->isAllowed($userGroup, $siteGroup, 'Edit'));
       $this->assertFalse($acl->isAllowed($userGroup, $siteGroup, 'Remove'));
    }

    public function testAggregateRoles()
    {
        $acl = new Acl;

        $user = new Role('User');
        $moderator = new Role('Moderator');
        $admin = new Role('Admin');

        $page = new Resource('Page');
        $blog = new Resource('Blog');
        $site = new Resource('Site');

        $userGroup = new RoleAggregate();

        $userGroup->addRole($user);
        $userGroup->addRole($moderator);
        $userGroup->addRole($admin);

        $acl->addRule($user, $page, new Rule('View'), true);
        $acl->addRule($moderator, $blog, new Rule('Edit'), true);
        $acl->addRule($admin, $site, new Rule('Remove'), true);

        $this->assertTrue($acl->isAllowed($userGroup, 'Page', 'View'));
        $this->assertTrue($acl->isAllowed($userGroup, 'Blog', 'Edit'));
        $this->assertTrue($acl->isAllowed($userGroup, 'Site', 'Remove'));
    }

    public function testAggregateResources()
    {
        $acl = new Acl;

        $user = new Role('User');
        $moderator = new Role('Moderator');
        $admin = new Role('Admin');

        $page = new Resource('Page');
        $blog = new Resource('Blog');
        $site = new Resource('Site');

        $siteGroup = new ResourceAggregate();

        $siteGroup->addResource($page);
        $siteGroup->addResource($blog);
        $siteGroup->addResource($site);

        $acl->addRule($user, $page, new Rule('View'), true);
        $acl->addRule($moderator, $blog, new Rule('Edit'), true);
        $acl->addRule($admin, $site, new Rule('Remove'), true);

        $this->assertTrue($acl->isAllowed('User', $siteGroup, 'View'));
        $this->assertTrue($acl->isAllowed('Moderator', $siteGroup, 'Edit'));
        $this->assertTrue($acl->isAllowed('Admin', $siteGroup, 'Remove'));
    }

    public function testAggregateRolesAndResources()
    {
        $acl = new Acl;

        $user = new Role('User');
        $moderator = new Role('Moderator');
        $admin = new Role('Admin');

        $page = new Resource('Page');
        $blog = new Resource('Blog');
        $site = new Resource('Site');

        $userGroup = new RoleAggregate();
        $userGroup->addRole($user);
        $userGroup->addRole($moderator);
        $userGroup->addRole($admin);

        $siteGroup = new ResourceAggregate();
        $siteGroup->addResource($page);
        $siteGroup->addResource($blog);
        $siteGroup->addResource($site);

        $acl->addRule($user, $page, new Rule('View'), true);
        $acl->addRule($moderator, $blog, new Rule('Edit'), true);
        $acl->addRule($admin, $site, new Rule('Remove'), true);

        $this->assertTrue($acl->isAllowed($userGroup, $siteGroup, 'View'));
        $this->assertTrue($acl->isAllowed($userGroup, $siteGroup, 'Edit'));
        $this->assertTrue($acl->isAllowed($userGroup, $siteGroup, 'Remove'));
    }

    public function testStringAsRule()
    {
        $acl = new Acl;

        $user = new Role('User');
        $resource = new Resource('Page');

        $acl->addRule($user, $resource, 'View', true);
        $acl->addRule($user, $resource, 'Edit', true);
        $acl->addRule($user, $resource, 'Remove', true);

        $this->assertTrue($acl->isAllowed('User', 'Page', 'View'));
        $this->assertTrue($acl->isAllowed('User', 'Page', 'Edit'));
        $this->assertTrue($acl->isAllowed('User', 'Page', 'Remove'));

        $acl = new Acl;

        $acl->setRuleClass('SimpleAcl\Rule');

        $user = new Role('User');
        $resource = new Resource('Page');

        $acl->addRule($user, $resource, 'View', false);
        $acl->addRule($user, $resource, 'Edit', false);
        $acl->addRule($user, $resource, 'Remove', false);

        $this->assertFalse($acl->isAllowed('User', 'Page', 'View'));
        $this->assertFalse($acl->isAllowed('User', 'Page', 'Edit'));
        $this->assertFalse($acl->isAllowed('User', 'Page', 'Remove'));
    }

    public function testGetResult()
    {
        $self = $this;

        $testReturnResult = function ($result, $expected) use ($self) {
            $index = 0;
            foreach ($result as $r) {
                $self->assertSame($expected[$index], $r->getRule());
                $index++;
            }
            $self->assertEquals(count($expected), $index);
        };

        $acl = new Acl;

        $user = new Role('User');
        $resource = new Resource('Page');

        $view = new Rule('View');
        $edit = new Rule('Edit');
        $remove = new Rule('Remove');

        $acl->addRule($user, $resource, $view, true);
        $acl->addRule($user, $resource, $edit, true);
        $acl->addRule($user, $resource, $remove, true);

        $this->assertTrue($acl->isAllowed('User', 'Page', 'View'));
        $this->assertTrue($acl->isAllowed('User', 'Page', 'Edit'));
        $this->assertTrue($acl->isAllowed('User', 'Page', 'Remove'));

        $testReturnResult($acl->isAllowedReturnResult('User', 'Page', 'View'), array($view));
        $testReturnResult($acl->isAllowedReturnResult('User', 'Page', 'Edit'), array($edit));
        $testReturnResult($acl->isAllowedReturnResult('User', 'Page', 'Remove'), array($remove));

        $acl = new Acl;

        $acl->addRule($user, $resource, $view, false);
        $acl->addRule($user, $resource, $edit, false);
        $acl->addRule($user, $resource, $remove, false);

        $this->assertFalse($acl->isAllowed('User', 'Page', 'View'));
        $this->assertFalse($acl->isAllowed('User', 'Page', 'Edit'));
        $this->assertFalse($acl->isAllowed('User', 'Page', 'Remove'));

        $testReturnResult($acl->isAllowedReturnResult('User', 'Page', 'View'), array($view));
        $testReturnResult($acl->isAllowedReturnResult('User', 'Page', 'Edit'), array($edit));
        $testReturnResult($acl->isAllowedReturnResult('User', 'Page', 'Remove'), array($remove));

        // test RuleResult order
        $acl = new Acl;

        $view1 = new Rule('View');
        $view2 = new Rule('View');
        $view3 = new Rule('View');
        $view4 = new Rule('View');

        $acl->addRule($user, $resource, $view, false);
        $acl->addRule($user, $resource, $view1, true);
        $acl->addRule($user, $resource, $view2, false);
        $acl->addRule($user, $resource, $view3, true);
        $acl->addRule($user, $resource, $view4, false);

        $testReturnResult($acl->isAllowedReturnResult('User', 'Page', 'View'), array($view4, $view3, $view2, $view1, $view));
    }

    public function testRuleWithNullActionNotCounts()
    {
        $acl = new Acl;

        $user = new Role('User');
        $resource = new Resource('Resource');

        $nullAction = new Rule('View');

        $acl->addRule($user, $resource, 'View', true);
        $acl->addRule($user, $resource, $nullAction, null);

        $this->assertTrue($acl->isAllowed('User', 'Resource', 'View'));
    }

    public function testActionCallable()
    {
        $acl = new Acl;

        $user = new Role('User');
        $resource = new Resource('Resource');

        $acl->addRule($user, $resource, 'View', function () {
            return true;
        });

        $this->assertTrue($acl->isAllowed('User', 'Resource', 'View'));
    }

    public function testSetAggregates()
    {
        $acl = new Acl();

        $u = new Role('U');
        $r = new Resource('R');

        $roleAgr = new RoleAggregate();
        $roleAgr->addRole($u);

        $resourceAgr = new ResourceAggregate();
        $resourceAgr->addResource($r);

        $self = $this;

        $rule = new Rule('View');

        $acl->addRule($u, $r, $rule, function (RuleResult $r) use ($roleAgr, $resourceAgr, $self) {
            $self->assertSame($roleAgr, $r->getRoleAggregate());
            $self->assertSame($resourceAgr, $r->getResourceAggregate());

            return true;
        });

        $this->assertTrue($acl->isAllowed($roleAgr, $resourceAgr, 'View'));

        $rule->setAction(function (RuleResult $r) use ($self) {
            $self->assertNull($r->getRoleAggregate());
            $self->assertNull($r->getResourceAggregate());

            return true;
        });

        $this->assertTrue($acl->isAllowed('U', 'R', 'View'));
    }
}
