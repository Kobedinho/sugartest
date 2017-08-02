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
 * SugarQuery Test Cases
 */
class SugarQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test subpanel joins
     *
     * FIXME: This unit test is not complete and primarily targets the fix for
     * BR-2039. SugarQuery also needs some refactoring for proper unit testing
     * as there are too many dependencies which cannot be properly injected
     * to mock and isolate the tests.
     *
     * @covers SugarQuery::joinSubPanel
     */
    public function testJoinSubpanel()
    {
        // Test settings
        $joinAlias = 'foobaralias';
        $linkName = 'bogus_link';
        $tableName = 'dummy';

        $joinParams = array(
            'joinTableAlias' => $joinAlias,
            'joinType' => 'INNER',
            'ignoreRole' => false,
            'reverse' => true,
            'includeCustom' => true,
        );

        // Link2 mock
        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('buildJoinSugarQuery'))
            ->getMock();

        $link->expects($this->once())
            ->method('buildJoinSugarQuery')
            ->with($this->anything(), $joinParams);

        // SugarBean mock
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();

        $bean->expects($this->any())
            ->method('load_relationship')
            ->will($this->returnValue(true));

        $bean->table_name = $tableName;
        $bean->$linkName = $link;

        // SugarQuery mock
        $query = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getJoinTableAlias'))
            ->getMock();

        $query->expects($this->once())
            ->method('getJoinTableAlias')
            ->with($linkName)
            ->will($this->returnValue($joinAlias));

        // Hack to satisfy the tests (no proper SugarQuery injection)
        $join = $this->getMockBuilder('SugarQuery_Builder_Join')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $join->query = $query;
        $query->join[$joinAlias] = $join;

        // Execute tests
        $query->joinSubPanel($bean, $linkName, array());
    }

    /**
     * @dataProvider dataProviderGetJoinOnField
     *
     * @param string $side
     * @param string $expected
     */
    public function testGetJoinOnField($side, $expected)
    {
        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipObject', 'getSide'))
            ->getMock();

        $rel = $this->getMockBuilder('M2MRelationship')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        SugarTestReflection::setProtectedValue($rel, 'def', array(
            'join_key_rhs' => 'right_hand_side_id',
            'join_key_lhs' => 'left_hand_side_id'
        ));

        $link2->expects($this->once())
            ->method('getRelationshipObject')
            ->willReturn($rel);

        $link2->expects($this->atLeastOnce())
            ->method('getSide')
            ->willReturn($side);

        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();

        $bean->expects($this->once())
            ->method('load_relationship')
            ->willReturn(true);

        $bean->test_link = $link2;

        $q->from = $bean;

        $actual = SugarTestReflection::callProtectedMethod($q, 'getJoinOnField', array('test_link'));

        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderGetJoinOnField()
    {
        return array(
            array('RHS', 'left_hand_side_id'),
            array('LHS', 'right_hand_side_id')
        );
    }
}
