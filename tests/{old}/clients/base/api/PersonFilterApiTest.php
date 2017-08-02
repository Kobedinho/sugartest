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
 * @group ApiTests
 */
class PersonFilterApiTest extends Sugar_PHPUnit_Framework_TestCase {

    public $personUnifiedSearchApi;

    public function setUp() {
        SugarTestHelper::setUp("current_user");        
        $this->personFilterApi = new PersonFilterApi();
    }

    public function tearDown() {
        SugarTestHelper::tearDown();
        parent::tearDown();        
    }

    // @Bug 61073
    public function testNoPortalUserReturned() {
        $GLOBALS['current_user']->portal_only = 1;
        $GLOBALS['current_user']->save();
        $args = array('module_list' => 'Users',);
        $list = $this->personFilterApi->globalSearch(new PersonFilterApiMockUp(), $args);
        $list = $list['records'];
        $expected = array();
        foreach($list AS $record) {
            $expected[] = $record['id'];
        }

        $this->assertTrue(!in_array($GLOBALS['current_user']->id, $expected));

    }

    public function testNoShowOnEmployees() {
        $GLOBALS['current_user']->show_on_employees = 0;
        $GLOBALS['current_user']->employee_status = 'Active';
        $GLOBALS['current_user']->save();
        $args = array('module_list' => 'Employees',);
        $list = $this->personFilterApi->globalSearch(new PersonFilterApiMockUp(), $args);
        $list = $list['records'];
        $expected = array();
        foreach($list AS $record) {
            $expected[] = $record['id'];
        }

        $this->assertTrue(!in_array($GLOBALS['current_user']->id, $expected));
    }

    public function testShowOnEmployees() {
        $GLOBALS['current_user']->show_on_employees = 1;
        $GLOBALS['current_user']->employee_status = 'Active';
        $GLOBALS['current_user']->save();
        $args = array('module_list' => 'Employees',);
        $list = $this->personFilterApi->globalSearch(new PersonFilterApiMockUp(), $args);
        $list = $list['records'];
        $expected = array();
        foreach($list AS $record) {
            $expected[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $expected));
    }


}

class PersonFilterApiMockUp extends RestService
{
    public function __construct() {$this->user = $GLOBALS['current_user'];}
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
