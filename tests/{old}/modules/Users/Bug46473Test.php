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



class Bug46473Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestIncomplete('This test will fail when the entire suite is run.  Probably needs mock objects for the list view objects');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Users');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['action'] = 'index';
        $GLOBALS['module'] = 'Users';
        $_REQUEST['module'] = 'Users';
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['action']);
        unset($GLOBALS['module']);
        unset($_REQUEST['module']);
    }

    public function testUserListView()
    {
        // new employee
        $last_name = 'Test_46473_'.time();
        $emp = new Employee();
        $emp->last_name = $last_name;
        $emp->default_team = 1;
        $emp->status = 'Active';
        $emp->employee_status = 'Active';
        $emp->user_name = 'test_user_name';
        $emp->save();
        $emp_id = $emp->id;
        $this->assertNotNull($emp_id, 'User id should not be null.');

        // list view
        $view = new UsersViewList();
        $view->module = 'Users';
        $view->init($emp);
        $view->lv = new ListViewSmarty();
        $view->display();

        // ensure the new employee shows up in the users list view
        $this->expectOutputRegex('/.*'.$last_name.'.*/');
    }
}

?>