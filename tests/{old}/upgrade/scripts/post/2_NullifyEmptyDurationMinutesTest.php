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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/2_NullifyEmptyDurationMinutes.php';

/**
 * Test for nullifying duration_minutes field on the database during upgrade
 */
class SugarUpgradeNullifyEmptyDurationMinutesTest extends UpgradeTestCase
{
    /**
     * Tests checking the column type
     * @param string $type
     * @param boolean $expect
     * @dataProvider columnTypeIsIntProvider
     */
    public function testColumnTypeIsInt($type, $expect)
    {
        $mock = $this->getUpgraderMock();

        $actual = SugarTestReflection::callProtectedMethod($mock, 'columnTypeIsInt', array($type));
        $this->assertEquals($actual, $expect);
    }

    /**
     * Tests getting the correct column type
     * @param array $columns
     * @param string $expect
     * @dataProvider getColumnTypeProvider
     */
    public function testGetColumnType($columns, $expect)
    {
        $mock = $this->getUpgraderMock(array('getColumns'));

        $mock->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue($columns));

        $actual = SugarTestReflection::callProtectedMethod($mock, 'getColumnType', array());
        $this->assertEquals($actual, $expect);
    }

    /**
     * Tests getting the correct field def type
     * @param array $defs
     * @param string $expect
     * @dataProvider getFielddefTypeProvider
     */
    public function testGetFielddefType($defs, $expect)
    {
        $mock = $this->getUpgraderMock(array('getFielddefsFromBean'));

        $mock->expects($this->any())
             ->method('getFielddefsFromBean')
             ->will($this->returnValue($defs));

        $actual = SugarTestReflection::callProtectedMethod($mock, 'getFielddefType', array());
        $this->assertEquals($actual, $expect);
    }

    /**
     * Tests whether the upgrader needs to run
     * @param array $columns
     * @param array $defs
     * @param boolean $expect
     * @dataProvider needsUpdatingProvider
     */
    public function testNeedsUpdating($columns, $defs, $expect)
    {
        $mock = $this->getUpgraderMock(array('getColumns', 'getFielddefsFromBean'));

        $mock->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue($columns));

        $mock->expects($this->any())
             ->method('getFielddefsFromBean')
             ->will($this->returnValue($defs));

        $actual = SugarTestReflection::callProtectedMethod($mock, 'needsUpdating', array());
        $this->assertEquals($actual, $expect);
    }

    public function columnTypeIsIntProvider()
    {
        return array(
            // Tests basic expectation
            array(
                'type' => 'int',
                'expect' => true,
            ),
            // Tests uppercase expectation
            array(
                'type' => 'INT',
                'expect' => true,
            ),
            // Tests Oracle expectation
            array(
                'type' => 'number',
                'expect' => true,
            ),
            // Tests Oracle UPPERCASE expectation
            array(
                'type' => 'NUMBER',
                'expect' => true,
            ),
            // Tests IBMDB2 expectation
            array(
                'type' => 'integer',
                'expect' => true,
            ),
            // Tests IBMDB2 UPPERCASE expectation
            array(
                'type' => 'INTEGER',
                'expect' => true,
            ),
            // Tests non-expectation in SQL SERVER (should be int)
            array(
                'type' => 'NUMERIC',
                'expect' => false,
            ),
            // Tests non-expectation
            array(
                'type' => 'enum',
                'expect' => false,
            ),
            // Tests column not on table
            array(
                'type' => '',
                'expect' => true,
            ),
            // Tests column not on table
            array(
                'type' => null,
                'expect' => true,
            ),
        );
    }

    public function getColumnTypeProvider()
    {
        return array(
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'type' => 'int',
                    ),
                ),
                'expect' => 'int',
            ),
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'type' => 'char',
                    ),
                ),
                'expect' => 'char',
            ),
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'custom_type' => 'int',
                    ),
                ),
                'expect' => null,
            ),
        );
    }

    public function getFielddefTypeProvider()
    {
        return array(
            // Test type is set to something
            array(
                'defs' => array(
                    'duration_minutes' => array(
                        'type' => 'int',
                    ),
                ),
                'expect' => 'int',
            ),
            // Test dbType is set to something
            array(
                'defs' => array(
                    'duration_minutes' => array(
                        'dbType' => 'char',
                    ),
                ),
                'expect' => 'char',
            ),
            // Test field not set returns null
            array(
                'defs' => array(
                    'duration' => array(
                        'type' => 'int',
                    ),
                ),
                'expect' => null,
            ),
            // Test type not set returns null
            array(
                'defs' => array(
                    'duration_minutes' => array(
                        'len' => 20,
                    ),
                ),
                'expect' => null,
            ),
        );
    }

    public function needsUpdatingProvider()
    {
        return array(
            // Test colType is int returns false
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'type' => 'int',
                    ),
                ),
                'defs' => array(
                    'duration_minutes' => array(
                        'type' => 'int',
                    ),
                ),
                'expect' => false,
            ),
            // Test colType is null return false
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'custom_type' => 'int',
                    ),
                ),
                'defs' => array(
                    'duration_minutes' => array(
                        'type' => 'int',
                    ),
                ),
                'expect' => false,
            ),
            // Test fieldType is int returns true
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'type' => 'char',
                    ),
                ),
                'defs' => array(
                    'duration_minutes' => array(
                        'type' => 'int',
                    ),
                ),
                'expect' => true,
            ),
            // Test fieldType is char (not int) returns false
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'type' => 'char',
                    ),
                ),
                'defs' => array(
                    'duration_minutes' => array(
                        'type' => 'char',
                    ),
                ),
                'expect' => false,
            ),
            // Test fieldType is null (not int) returns false
            array(
                'columns' => array(
                    'duration_minutes' => array(
                        'type' => 'char',
                    ),
                ),
                'defs' => array(
                    'duration_minutes' => array(
                        'custom_type' => 'tinyint',
                    ),
                ),
                'expect' => false,
            ),
        );

    }

    public function getUpgraderMock($methods = null)
    {
        return $this->getMockBuilder('SugarUpgradeNullifyEmptyDurationMinutes')
                    ->disableOriginalConstructor()
                    ->setMethods($methods)
                    ->getMock();
    }
}
