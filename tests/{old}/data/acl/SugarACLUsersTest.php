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

class SugarACLUsersTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $sugarConfigBackup = array();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        global $sugar_config;
        $this->sugarConfigBackup = $sugar_config;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        global $sugar_config;
        $sugar_config = $this->sugarConfigBackup;
    }

    /**
     * Data provider for testCheckAccess.
     *
     * @see SugarACLUsersTest::testCheckAccess
     * @return array
     */
    public static function checkAccessDataProvider()
    {
        return array(
            // We can get it only from Users/Employees module
            array('Accounts', 'view', false, array(), array(), false),
            // Let the other modules decide about acl access
            array('Users', 'team_security', false, array(), array(), true),
            // Regular user in any way shouldn't have access to export functionality
            array('Users', 'export', false, array(),
                array('disable_export' => false, 'admin_export_only' => false), false),
            // Regular user shouldn't have access to export functionality in Employees module
            array('Employees', 'export', false, array(),
                array('disable_export' => false, 'admin_export_only' => false), false),
            // Regular user doesn't have access to export functionality if admin_export_only is true
            array('Employees', 'export', false, array(),
                array('disable_export' => false, 'admin_export_only' => true), false),
            // Admin user has access to export
            array('Users', 'export', true, array(),
                array('disable_export' => false, 'admin_export_only' => true), true),
            // Admin user doesn't have access to export if disable_export is true
            array('Users', 'export', true, array(),
                array('disable_export' => true, 'admin_export_only' => true), false),
            // This is another way to disable yourself
            array('Users', 'field', false, array('action' => 'edit', 'field' => 'status'), array(), false),
            array('Users', 'field', false, array('action' => 'massupdate', 'field' => 'status'), array(), false),
            array('Users', 'field', false, array('action' => 'delete', 'field' => 'status'), array(), false),
        );
    }

    /**
     * Test some common cases for check access method
     *
     * @dataProvider checkAccessDataProvider
     * @covers SugarACLUsers::CheckAccess
     * @param string $module Module name
     * @param string $view View name
     * @param bool $isAdmin
     * @param array $context
     * @param array $config additional values
     * @param bool $expected Expected result
     */
    public function testCheckAccess($module, $view, $isAdmin, $context, $config, $expected)
    {
        SugarTestHelper::setUp('current_user', array(true, $isAdmin));

        // Set config parameters
        global $sugar_config;
        foreach ($config as $key => $value) {
            $sugar_config[$key] = $value;
        }

        /** @var SugarACLUsers|PHPUnit_Framework_MockObject_MockObject $acl_class */
        $acl_class = $this->getMockBuilder('SugarACLUsers')->setMethods(null)->getMock();
        $result = $acl_class->checkAccess($module, $view, $context);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testAccessToDelete.
     *
     * @see SugarACLUsersTest::testAccessToDelete
     * @return array
     */
    public static function accessToDeleteDataProvider()
    {
        return array(
            // You can not delete yourself even if you admin
            array(true, array(), true, false),
            // You can delete somebody if you admin
            array(true, array(), false, true),
            // You can not delete yourself (regular user)
            array(false, array(), true, false),
            // You can not delete somebody (regular user)
            array(false, array(), false, false),
        );
    }

    /**
     * Check specific cases related to delete action
     *
     * @dataProvider accessToDeleteDataProvider
     * @covers SugarACLUsers::CheckAccess
     * @param bool $isAdmin
     * @param array $context
     * @param bool $isMyself Expected result for myselfCheck method
     * @param bool $expected Expected result
     */
    public function testAccessToDelete($isAdmin, $context, $isMyself, $expected)
    {
        SugarTestHelper::setup('current_user', array(true, $isAdmin));

        /** @var SugarACLUsers|PHPUnit_Framework_MockObject_MockObject $acl_class */
        $acl_class = $this->createPartialMock('SugarACLUsers', array('myselfCheck'));
        $acl_class->expects($this->once())
            ->method('myselfCheck')
            ->will($this->returnValue($isMyself));

        // You can not delete yourself even if you admin
        $result = $acl_class->checkAccess('Users', 'delete', $context);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testAccessToEditYourself.
     *
     * @see SugarACLUsersTest::testAccessToEditYourself
     * @return array
     */
    public static function accessToEditDataProvider()
    {
        return array(
            // You can edit youself
            array(false, array(), true, true),
            // You can not edit somebody if you are not admin
            array(false, array(), false, false),
            // You can edit user profile if you are admin
            array(true, array(), false, true),
            // You can edit user profile if you are admin
            array(true, array(), true, true),
        );
    }

    /**
     * Check specific cases related to edit action
     *
     * @dataProvider accessToEditDataProvider
     * @covers SugarACLUsers::CheckAccess
     * @param bool $isAdmin
     * @param array $context
     * @param bool $isMyself Expected result for myselfCheck method
     * @param bool $expected Expected result
     */
    public function testAccessToEditYourself($isAdmin, $context, $isMyself, $expected)
    {
        SugarTestHelper::setup('current_user', array(true, $isAdmin));

        /** @var SugarACLUsers|PHPUnit_Framework_MockObject_MockObject $acl_class */
        $acl_class = $this->createPartialMock('SugarACLUsers', array('myselfCheck'));
        $acl_class->expects($this->once())
            ->method('myselfCheck')
            ->will($this->returnValue($isMyself));

        $result = $acl_class->checkAccess('Users', 'edit', $context);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test functionality of comparing some user with current signed in user
     * @covers SugarACLUsers::myselfCheck
     */
    public function testMyselfCheck()
    {
        $current_user = SugarTestHelper::setup('current_user', array(true, false));

        /** @var SugarACLUsers|PHPUnit_Framework_MockObject_MockObject $acl_class */
        $acl_class = $this->createMock('SugarACLUsers');

        /** @var User|PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->createMock('Users');

        // Expected result - false if bean id and current_user id are not equal
        $this->assertFalse($acl_class->myselfCheck($bean, $current_user));

        // Expected result - true if bean id and current_user id are equal
        $bean->id = $current_user->id;
        $this->assertTrue($acl_class->myselfCheck($bean, $current_user));
    }

    /**
     * Data provider for testCheckFieldList.
     *
     * @covers SugarACLUsersTest::testCheckFieldList
     * @return array
     */
    public static function checkFieldListDataProvider()
    {
        return array(
            // no_access_fields && no_edit_fields have already been checked in testAccessToFields
            array(false, 'Users', array('status', 'employee_status'), 'edit', array(), true, array(false, false)),
            array(false, 'Users', array('status', 'employee_status'), 'massupdate', array(), true, array(false, false)),
            array(false, 'User', array('status', 'employee_status'), 'delete', array(), true, array(false, false)),
            array(true, 'Users', array('user_hash', 'password'), 'field', array(), false, array(true, true)),
            array(false, 'Users', array('user_hash', 'password'), 'field', array(), false, array(false, false)),
        );
    }

    /**
     * Test field list check
     *
     * @dataProvider checkFieldListDataProvider
     * @covers SugarACLUsers::checkFieldList
     * @param bool $isAdmin
     * @param string $module
     * @param array $field_list
     * @param string $action
     * @param array $context
     * @param bool $isMyself
     * */
    public function testCheckFieldList($isAdmin, $module, $field_list, $action, $context, $isMyself, $expected)
    {
        SugarTestHelper::setup('current_user', array(true, $isAdmin));

        /** @var SugarACLUsers|PHPUnit_Framework_MockObject_MockObject $acl_class */
        $acl_class = $this->createPartialMock('SugarACLUsers', array('myselfCheck'));
        $acl_class->expects($this->once())
            ->method('myselfCheck')
            ->will($this->returnValue($isMyself));

        // You can not delete yourself even if you admin
        $result = $acl_class->checkFieldList($module, $field_list, $action, $context);
        $this->assertEquals($expected, $result);
    }
}
