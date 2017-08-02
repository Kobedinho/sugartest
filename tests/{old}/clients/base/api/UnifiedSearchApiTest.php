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

require_once 'tests/{old}/SugarTestRestUtilities.php';
require_once 'tests/{old}/SugarTestACLUtilities.php';
/**
 * @group ApiTests
 */
class UnifiedSearchApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $accounts;
    public $roles;
    public $unifiedSearchApi;
    public $moduleApi;
    public $serviceMock;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp('ACLStatic');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        // create a bunch of accounts
        for ($x=0; $x<10; $x++) {
            $acc = BeanFactory::newBean('Accounts');
            $acc->name = 'UnifiedSearchApiTest Account ' . create_guid();
            $acc->assigned_user_id = $GLOBALS['current_user']->id;
            $acc->save();
            $this->accounts[] = $acc;
        }
        // load up the unifiedSearchApi for good times ahead
        $this->unifiedSearchApi = new UnifiedSearchApi();
        $this->moduleApi = new ModuleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        $GLOBALS['current_user']->is_admin = 1;
        // delete the bunch of accounts crated
        foreach ($this->accounts AS $account) {
            $account->mark_deleted($account->id);
        }
        // unset unifiedSearchApi
        unset($this->unifiedSearchApi);
        unset($this->moduleApi);
        // clean up all roles created
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    // test that when read only is set for every field you can still retrieve
    // @Bug 60225
    public function testReadOnlyFields()
    {
        // create role that is all fields read only
        $role = SugarTestACLUtilities::createRole('UNIFIEDSEARCHAPI - UNIT TEST ' . create_guid(), array('Accounts'), array('access', 'view', 'list', 'export'));

        // get all the accounts fields and set them readonly
        foreach ($this->accounts[0]->field_defs AS $fieldName => $params) {
            SugarTestACLUtilities::createField($role->id, "Accounts", $fieldName, 50);
        }

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();
        // test I can retreive accounts
        $args = array('module_list' => 'Accounts',);
        $list = $this->unifiedSearchApi->globalSearch($this->serviceMock, $args);
        $this->assertNotEmpty($list['records'], "Should have some accounts: " . print_r($list, true));
    }

    // if you have view only you shouldn't be able to create, but you should be able to retrieve records
    public function testViewOnly()
    {
        // create a role that is view only
        $role = SugarTestACLUtilities::createRole('UNIFIEDSEARCHAPI - UNIT TEST ' . create_guid(), array('Accounts', ), array('access', 'view', 'list', ));

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        // test I can retrieve accounts
        $args = array('module_list' => 'Accounts',);
        $list = $this->unifiedSearchApi->globalSearch($this->serviceMock, $args);
        $this->assertNotEmpty($list['records'], "Should have some accounts: " . print_r($list, true));
        // test I can't create
        $this->setExpectedException(
          'SugarApiExceptionNotAuthorized', 'You are not authorized to create Accounts. Contact your administrator if you need access.'
        );
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'UnifiedSearchApi Create Denied - ' . create_guid()));
    }
}
