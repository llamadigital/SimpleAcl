<?php
namespace SimpleAclTest;

use PHPUnit_Framework_TestCase;

use SimpleAcl\Object;
use SimpleAcl\Object\RecursiveIterator;
use RecursiveIteratorIterator;

class RecursiveIteratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $name
     *
     * @return Object
     */
    protected function getObject($name)
    {
        return $this->getMockForAbstractClass('SimpleAcl\Object', array($name));
    }

    public function testKey()
    {
        $iterator = new RecursiveIterator(array());
        $this->assertNull($iterator->key());

        $iterator = new RecursiveIterator(array($this->getObject('Test')));
        $this->assertEquals('Test', $iterator->key());
    }

    public function testCurrent()
    {
        $iterator = new RecursiveIterator(array());
        $this->assertFalse($iterator->current());

        $test = $this->getObject('Test');
        $iterator = new RecursiveIterator(array($test));
        $this->assertSame($test, $iterator->current());
    }

    public function testValidNextRewind()
    {
        $iterator = new RecursiveIterator(array());
        $this->assertFalse($iterator->valid());

        $test1 = $this->getObject('Test1');
        $test2 = $this->getObject('Test2');

        $iterator = new RecursiveIterator(array($test1, $test2));
        $this->assertTrue($iterator->valid());
        $this->assertSame($test1, $iterator->current());
        $this->assertEquals('Test1', $iterator->key());

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertSame($test2, $iterator->current());
        $this->assertEquals('Test2', $iterator->key());
    }
}
