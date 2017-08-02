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
 * @ticket 56904
 */
class Bug56904Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Ensures that relationships for all related beans are removed and return
     * value is calculated based on the related beans remove results
     *
     * @param array $results
     * @param bool $expected
     * @dataProvider getRemoveResults
     */
    public function testAllRelationsAreRemoved(array $results, $expected)
    {
        $relationship = $this->getRelationshipMock($results);
        $link         = $this->getLinkMock(count($results));

        $result = $relationship->removeAll($link);
        if ($expected)
        {
            $this->assertTrue($result);
        }
        else
        {
            $this->assertFalse($result);
        }
    }

    /**
     * Creates mock of SugarRelationship object which will return specified
     * results on on consecutive SugarRelationship::remove() calls
     *
     * @param array $results
     * @return SugarRelationship
     */
    protected function getRelationshipMock(array $results)
    {
        $mock = $this->getMockForAbstractClass('SugarRelationship');
        $mock->expects($this->exactly(count($results)))
            ->method('remove')
            ->will(
                call_user_func_array(array($this, 'onConsecutiveCalls'), $results)
            );
        return $mock;
    }

    /**
     * Creates mock of Link2 object with specified number of related beans
     *
     * @param int $count
     * @return Link2
     */
    protected function getLinkMock($count)
    {
        if ($count > 0)
        {
            $bean  = new SugarBean();
            $bean->id = 'Bug56904Test';
            $beans = array_fill(0, $count, $bean);
        }
        else
        {
            $beans = array();
        }

        $mock = $this->createPartialMock('Link2', array('getSide', 'getFocus', 'getBeans'));
        $mock->expects($this->any())
            ->method('getSide')
            ->will($this->returnValue(REL_LHS));
        $mock->expects($this->any())
            ->method('getFocus')
            ->will($this->returnValue(new SugarBean()));
        $mock->expects($this->any())
            ->method('getBeans')
            ->will($this->returnValue($beans));
        return $mock;
    }

    /**
     * Provides results that should be returned by SugarRelationship::remove()
     * calls and expected result of SugarRelationship::removeAll()
     *
     * @return array
     */
    public static function getRemoveResults()
    {
        return array(
            array(
                array(), true,
            ),
            array(
                array(true), true,
            ),
            array(
                array(false), false,
            ),
            array(
                array(true, false), false,
            ),
            array(
                array(false, true), false,
            ),
            array(
                array(false, false), false,
            ),
            array(
                array(true, true), true,
            ),
        );
    }
}
