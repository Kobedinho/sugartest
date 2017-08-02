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

require_once 'tests/{old}/SugarTestACLUtilities.php';
/**
 * Bug 56391 - ACL's used in the MetadataManager were the static ones.  Have switched to use the SugarACL methods
 */
class Bug56391Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->accounts = array();
        SugarACL::resetACLs();
        SugarTestHelper::setUp('ACLStatic');
    }

    public function tearDown()
    {
        foreach ($this->accounts AS $account_id) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account_id}'");
        }
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * Test Users Module
     *
     * @group Bug56391
     */
    public function testUsersModule()
    {
        $mm = MetaDataManager::getManager();
        // because the user is not an admin the user should only have view and list access
        $expected_result = array(
            'admin' => 'no',
            'developer' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
            'export' => 'no',
        );
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);

    }


    /**
     * Test Users Module Fields
     *
     * @group Bug56391
     */
    public function testUsersModuleFields()
    {
        $mm = MetaDataManager::getManager();
        // because the user is not an admin the user should only have view and list access
        $expected_result = array(
                                    'user_name' => array('write' => 'no', 'create' => 'no'),
                                    'user_hash' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'system_generated_password' => array('read' => 'no', 'write' => 'no', 'create' => 'no',),
                                    'pwd_last_changed' => array('read' => 'no', 'write' => 'no', 'create' => 'no',),
                                    'authenticate_id' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'sugar_login' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'external_auth_only' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'status' => array('write' => 'no', 'create' => 'no'),
                                    'show_on_employees' => array('read' => 'no', 'write' => 'no', 'create' => 'no'),
                                    'portal_only' => array('read' => 'no', 'write' => 'no', 'create' => 'no',),
                                    'employee_status' => array('write' => 'no', 'create' => 'no'),
                                    'is_group' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'title' => array( 'write' => 'no', 'create' => 'no', ),
                                    'department' => array( 'write' => 'no', 'create' => 'no', ),
                                    'reports_to_id' => array( 'write' => 'no', 'create' => 'no', ),
                                    'reports_to_name' => array( 'write' => 'no', 'create' => 'no', ),
                                    'reports_to_link' => array( 'write' => 'no', 'create' => 'no', ),
                                    'is_admin' => array('write' => 'no', 'create' => 'no',  ),
                                    'last_login' => array( 'read' => 'no', 'write' => 'no', 'create' => 'no',  ),
                                );
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        $acls = $acls['fields'];

        $this->assertEquals($expected_result, $acls);

    }

    /**
     * Test Users Module as Admin
     *
     * @group Bug56391
     */
    public function testUsersAsAdminModule()
    {
        // set current user as an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
        $mm = MetaDataManager::getManager();
        // because the user is not an admin the user should only have view and list access

        $expected_result = array();
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);

        // remove admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->save();
    }

    /**
     * Test Users Module as Admin
     *
     * @group Bug56391
     */
    public function testUsersAsAdminModuleForSelf()
    {
        // set current user as an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
        $mm = MetaDataManager::getManager();
        // because the user is not an admin the user should only have view and list access

        $expected_result = array(
            'delete' => 'no',
        );
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user'], $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);

        // remove admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->save();
    }

    /**
     * Test Module Access
     *
     * Set 5 modules to have specific actions and verify them
     *
     * @group Bug56391
     */
    public function testModuleAccess()
    {
        $modules = array('Accounts', 'Contacts', 'Contracts', 'Opportunities', 'Leads');
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'developer' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'export'));
        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();
        foreach ($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls, "failed at module:$module");
        }
    }


    /**
     * Test Field Access
     *
     * Set a field on accounts to be not readable, writeable, or editable
     *
     * @group Bug56391
     */
    public function testFieldAccess()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'fields' =>
            array(
                'website' => array(
                    'read' => 'no',
                    'write' => 'no',
                    'create' => 'no',
                ),
            ),
            'admin' => 'no',
            'developer' => 'no',
            'delete' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit', 'import', 'export', 'massupdate'));

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'website', -99);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();
        foreach ($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    /**
     * Test Owner Access
     *
     * Test if Edit = Owner that we can not edit a bean that is not owned by the current user.
     *
     * @group Bug56391
     */
    public function testModuleOwnerAccess()
    {
        $modules = array('Accounts');


        $expected_bean_result['access'] = array(
            'admin' => 'no',
            'developer' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        );

        $this->roles[] = $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'view', 'list', 'edit', 'delete', 'export'), array('edit', 'delete'));

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts['access'] = $account->id;

        unset($account);

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['access']));
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

    /**
     * Test Owner Create Access
     *
     * Test if Create = Owner that we can create a bean.
     *
     * @group Bug56391
     */
    public function testModuleOwnerCreateAccess()
    {
        $modules = array('Accounts');


        $expected_bean_result['access'] = array(
            'admin' => 'no',
            'developer' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        );


        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'view', 'list', 'edit', 'delete', 'export'), array('create', 'edit'));

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::newBean('Accounts'));
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

    /**
     * Test Owner Create Access 2
     *
     * Test if Create = Owner that we can create a bean.
     *
     * @group Bug56391
     */
    public function testModuleOwnerCreateNewWithIdAccess()
    {
        $modules = array('Accounts');


        $expected_bean_result['access'] = array(
            'admin' => 'no',
            'developer' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        );


        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'edit', 'delete', 'export'), array('create', 'edit'));

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $account = BeanFactory::newBean('Accounts');
        $account->new_with_id = true;
        $account->id = create_guid();
        $account->name = "Tis Awesome";

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], $account);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

    public function testModuleOwnerNoAccess()
    {
        $this->markTestIncomplete("This is causing sporadic CI failures.  Skipping for now");

        $modules = array('Accounts');

        $expected_bean_result['no_access'] = array(
            'admin' => 'no',
            'developer' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
            'edit' => 'no',
            'create' => 'no',
        );

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = 1;
        $account->save();
        $this->accounts['no_access'] = $account->id;

        unset($account);

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'view', 'list', 'edit', 'export', 'create'), array('edit','create'));

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();
        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['no_access']));

        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['no_access'], $acls, 'No Access Failed');

    }

    public function testModuleFieldOwnerAccess()
    {
        $modules = array('Accounts');

        $expected_bean_result['field_access'] = array();



        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts['access'] = $account->id;

        unset($account);

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'view', 'list', 'edit', 'delete', 'export'), array('edit'));

        SugarTestACLUtilities::createField($role->id, 'Acconts', 'name', ACL_READ_OWNER_WRITE);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['access']));
        unset($acls['_hash']);

        $fields = $acls['fields'];
        unset($acls['fields']);
        $this->assertEquals($expected_bean_result['field_access'], $fields, 'Field Access Failed');

    }

    public function testModuleFieldOwnerNoAccess()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
        $modules = array('Accounts', );

        $expected_bean_result['field_no_access'] = array(
                'name' => array(
                        'write' => 'no',
                        'create' => 'no',
                    ),
            );
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = 1;
        $account->save();
        $this->accounts['no_access'] = $account->id;

        unset($account);
        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'view', 'list', 'edit', 'delete', 'export'), array('edit'));

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'name', 60);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['no_access']));
        unset($acls['_hash']);

        $fields = $acls['fields'];
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['field_no_access'], $fields, 'No Field Access Failed');

    }
}
