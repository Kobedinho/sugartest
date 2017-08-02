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

namespace Sugarcrm\SugarcrmTestsUnit\data;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

require_once 'data/Relationships/SugarRelationship.php';

/**
 * @coversDefaultClass M2MRelationship
 */
class Link2Test extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['log'] = $this->getMock('\LoggerManager', [], [], '', false);
    }

    protected function tearDown()
    {
        unset($GLOBALS['log']);
        parent::tearDown();
    }

    public function getSideDataProvider()
    {
        return [
            [REL_LHS, 'Tasks', 'tasks_link', [], 'tasks_link', 'Tasks', null, null, []],
            [REL_LHS, 'Employees', 'users_link', [], 'users_link', 'Users', null, null, []],
            [REL_RHS, 'Tasks', 'tasks_link', [], null, null, 'tasks_link', 'Tasks', []],
            [REL_RHS, 'Employees', 'users_link', [], null, null, 'users_link', 'Users', []],
            [REL_LHS, 'Tasks', 'link_name', ['side' => 'left'], 'tasks_link', 'Tasks', null, null, []],
            [REL_LHS, 'Tasks', 'link_name', ['side' => REL_LHS], 'tasks_link', 'Tasks', null, null, []],
            [REL_RHS, 'Tasks', 'link_name', ['side' => 'right'], 'tasks_link', 'Tasks', null, null, []],
            [
                REL_RHS,
                'Tasks',
                'link_name',
                ['id_name' => 'left_id'],
                null,
                null,
                'tasks_link',
                'Tasks',
                ['join_key_lhs' => 'left_id'],
            ],
            [
                REL_LHS,
                'Tasks',
                'link_name',
                ['id_name' => 'right_id'],
                'tasks_link',
                'Tasks',
                null,
                null,
                ['join_key_rhs' => 'right_id'],
            ],
            [REL_LHS, 'Tasks', 'link_name', [], 'tasks_link', 'Tasks', null, null, []],
            [REL_LHS, 'Employees', 'link_name', [], 'users_link', 'Users', null, null, []],
            [REL_RHS, 'Tasks', 'link_name', [], null, null, 'tasks_link', 'Tasks', []],
            [REL_RHS, 'Employees', 'link_name', [], null, null, 'users_link', 'Users', []],
            [REL_TYPE_UNDEFINED, 'Tasks', 'link_name', [], 'bad_link', 'Accounts', null, null, []],
            [REL_TYPE_UNDEFINED, 'Tasks', 'link_name', [], 'bad_link_1', 'Tasks', 'bad_link_2', 'Tasks', []],
        ];
    }

    /**
     * @dataProvider getSideDataProvider
     * @covers ::getSide()
     */
    public function testGetSide(
        $expected,
        $module,
        $linkName,
        $linkDef,
        $relLHSLink,
        $relLHSModule,
        $relRHSLink,
        $relRHSModule,
        $relDef
    ) {
        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        TestReflection::setProtectedValue($link, 'def', $linkDef);
        TestReflection::setProtectedValue($link, 'name', $linkName);
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $bean->module_name = $module;
        TestReflection::setProtectedValue($link, 'focus', $bean);
        $relationship = $this->getMockForAbstractClass(
            'SugarRelationship',
            [],
            '',
            false,
            false,
            true,
            ['getLHSLink', 'getLHSModule', 'getRHSLink', 'getRHSModule']
        );
        $relationship->expects($this->any())
            ->method('getLHSLink')
            ->will($this->returnValue($relLHSLink));
        $relationship->expects($this->any())
            ->method('getLHSModule')
            ->will($this->returnValue($relLHSModule));
        $relationship->expects($this->any())
            ->method('getRHSLink')
            ->will($this->returnValue($relRHSLink));
        $relationship->expects($this->any())
            ->method('getRHSModule')
            ->will($this->returnValue($relRHSModule));
        TestReflection::setProtectedValue($relationship, 'def', $relDef);
        TestReflection::setProtectedValue($link, 'relationship', $relationship);
        $this->assertEquals($expected, $link->getSide());
    }
}
