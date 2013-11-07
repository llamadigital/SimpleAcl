<?php
namespace SimpleAclTest;

use PHPUnit_Framework_TestCase;

use SimpleAcl\Object;

class ObjectTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        /** @var Object $object  */
        $object = $this->getMockForAbstractClass('SimpleAcl\Object', array('TestName'));

        $this->assertEquals($object->getName(), 'TestName');
        $object->setName('NewName');
        $this->assertEquals($object->getName(), 'NewName');
    }
}
