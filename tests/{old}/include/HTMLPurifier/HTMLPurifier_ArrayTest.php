<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */



/**
 * Bug #61818
 * Cron job hangs over minutes with CPU 100% during Email Import
 *
 * @ticket 61818
 */
class HTMLPurifier_ArrayTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Data provider for the rest of tests
     * @return array
     */
    public function getData()
    {
        return array(
            array(array()),
            array(array(1, 2, 3, 4))
        );
    }

    /**
     * Testing of initialization of properties of HTMLPurifier_Array
     *
     * @dataProvider getData
     */
    public function testConstruct($array)
    {
        $object = new HTMLPurifier_ArrayMock($array);

        $this->assertEquals(0, $object->getOffset());
        $this->assertEquals($object->getHead(), $object->getOffsetItem());
        $this->assertEquals(count($array), $object->getCount());
        $this->assertEquals($array, $object->getArray());
    }

    /**
     * Testing of offset & offsetItem properties while seeking/removing/inserting
     */
    public function testFindIndex()
    {
        $array = array(1, 2, 3, 4, 5);
        $object = new HTMLPurifier_ArrayMock($array);
        for ($i = 0; $i < $object->getCount(); $i ++) {
            $object[$i];
            $this->assertEquals($i, $object->getOffset());
            $this->assertEquals($array[$i], $object->getOffsetItem()->value);
        }

        $object[2];
        $this->assertEquals(2, $object->getOffset());
        $this->assertEquals(3, $object->getOffsetItem()->value);
        $object->remove(2);
        $this->assertEquals(2, $object->getOffset());
        $this->assertEquals(4, $object->getOffsetItem()->value);

        $object[1];
        $this->assertEquals(1, $object->getOffset());
        $this->assertEquals(2, $object->getOffsetItem()->value);
        $object->insertBefore(1, 'a');
        $this->assertEquals(1, $object->getOffset());
        $this->assertEquals('a', $object->getOffsetItem()->value);
    }

    /**
     * Testing that behavior of insertBefore the same as array_splice
     *
     * @dataProvider getData
     */
    public function testInsertBefore($array)
    {
        $object = new HTMLPurifier_ArrayMock($array);

        $index = 0;
        array_splice($array, $index, 0, array('a'));
        $object->insertBefore($index, 'a');
        $this->assertEquals($array, $object->getArray());

        $index = 2;
        array_splice($array, $index, 0, array('a'));
        $object->insertBefore($index, 'a');
        $this->assertEquals($array, $object->getArray());

        $index = count($array) * 2;
        array_splice($array, $index, 0, array('a'));
        $object->insertBefore($index, 'a');
        $this->assertEquals($array, $object->getArray());
    }

    /**
     * Testing that behavior of remove the same as array_splice
     *
     * @dataProvider getData
     */
    public function testRemove($array)
    {
        $object = new HTMLPurifier_ArrayMock($array);

        $index = 0;
        array_splice($array, $index, 1);
        $object->remove($index);
        $this->assertEquals($array, $object->getArray());

        $index = 2;
        array_splice($array, $index, 1);
        $object->remove($index);
        $this->assertEquals($array, $object->getArray());

        $index = count($array) * 2;
        array_splice($array, $index, 1);
        $object->remove($index);
        $this->assertEquals($array, $object->getArray());
    }

    /**
     * Testing that behavior of arraySplice the same as array_splice
     *
     * @dataProvider getData
     */
    public function testArraySplice($array)
    {
        $object = new HTMLPurifier_ArrayMock($array);

        $replacement = array(rand(10, 100), rand(10, 100), rand(10, 100));
        $returnArray = array_splice($array, 0, 0, $replacement);
        $returnObject = $object->splice(0, 0, $replacement);
        $this->assertEquals($returnArray, $returnObject);
        $this->assertEquals($array, $object->getArray());

        $replacement = array(rand(10, 100), rand(10, 100), rand(10, 100));
        $returnArray = array_splice($array, 0, 1, $replacement);
        $returnObject = $object->splice(0, 1, $replacement);
        $this->assertEquals($returnArray, $returnObject);
        $this->assertEquals($array, $object->getArray());

        $replacement = array(rand(10, 100), rand(10, 100), rand(10, 100));
        $returnArray = array_splice($array, 1, 0, $replacement);
        $returnObject = $object->splice(1, 0, $replacement);
        $this->assertEquals($returnArray, $returnObject);
        $this->assertEquals($array, $object->getArray());

        $replacement = array(rand(10, 100), rand(10, 100), rand(10, 100));
        $returnArray = array_splice($array, 1, 1, $replacement);
        $returnObject = $object->splice(1, 1, $replacement);
        $this->assertEquals($returnArray, $returnObject);
        $this->assertEquals($array, $object->getArray());

        $replacement = array(rand(10, 100), rand(10, 100), rand(10, 100));
        $returnArray = array_splice($array, 1, 2, $replacement);
        $returnObject = $object->splice(1, 2, $replacement);
        $this->assertEquals($returnArray, $returnObject);
        $this->assertEquals($array, $object->getArray());

        $length = count($array) + 1;
        $replacement = array(rand(10, 100), rand(10, 100), rand(10, 100));
        $returnArray = array_splice($array, $length, 0, $replacement);
        $returnObject = $object->splice($length, 0, $replacement);
        $this->assertEquals($returnArray, $returnObject);
        $this->assertEquals($array, $object->getArray());

        $length = count($array) + 1;
        $replacement = array(rand(10, 100), rand(10, 100), rand(10, 100));
        $returnArray = array_splice($array, $length, 2, $replacement);
        $returnObject = $object->splice($length, 2, $replacement);
        $this->assertEquals($returnArray, $returnObject);
        $this->assertEquals($array, $object->getArray());
    }

    /**
     * Testing that object returns original array
     *
     * @dataProvider getData
     */
    public function testGetArray($array)
    {
        $object = new HTMLPurifier_ArrayMock($array);
        $this->assertEquals($array, $object->getArray());
    }

    /**
     * Testing ArrayAccess interface
     *
     * @dataProvider getData
     */
    public function testOffsetExists($array)
    {
        $object = new HTMLPurifier_ArrayMock($array);
        $this->assertEquals(isset($array[0]), isset($object[0]));
    }

    /**
     * Testing ArrayAccess interface
     */
    public function testOffsetGet()
    {
        $array = array(1, 2, 3);
        $object = new HTMLPurifier_ArrayMock($array);
        foreach ($array as $k => $v) {
            $this->assertEquals($v, $object[$k]);
        }
    }

    /**
     * Testing ArrayAccess interface
     */
    public function testOffsetSet()
    {
        $array = array(1, 2, 3);
        $object = new HTMLPurifier_ArrayMock($array);
        foreach ($array as $k => $v) {
            $v = $v * 2;
            $object[$k] = $v;
            $this->assertEquals($v, $object[$k]);
        }
    }

    /**
     * Testing ArrayAccess interface
     * There is one difference: keys are updated as well, they are started from 0
     */
    public function testOffsetUnset()
    {
        $object = new HTMLPurifier_ArrayMock(array(1, 2, 3, 4));
        unset($object[1]);
        $this->assertEquals(array(1, 3, 4), $object->getArray());
        unset($object[0]);
        $this->assertEquals(array(3, 4), $object->getArray());
        unset($object[1]);
        $this->assertEquals(array(3), $object->getArray());
        unset($object[0]);
        $this->assertEquals(array(), $object->getArray());
    }

    /**
     * Testing behavior when Array goes to zero size
     */
    public function testZeroSize()
    {
        $object = new HTMLPurifier_ArrayMock(array(1));

        $object->remove(0);
        $this->assertNull($object->getHead());
        $this->assertNull($object->getOffsetItem());
        $this->assertEquals(0, $object->getCount());
        $this->assertEquals(array(), $object->getArray());

        $object->insertBefore(0, 1);
        $this->assertEquals(array(1), $object->getArray());
    }
}

/**
 * Mock for some protected properties of HTMLPurifier_Array
 */
class HTMLPurifier_ArrayMock extends HTMLPurifier_Array
{
    /**
     * @return HTMLPurifier_ArrayNode|null
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return HTMLPurifier_ArrayNode|null
     */
    public function getOffsetItem()
    {
        return $this->offsetItem;
    }
}
