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
 * ACL's
 */
class GetAclForModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $roles = array();
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->accounts = array();
        SugarACL::$acls = array();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        foreach($this->accounts AS $account_id) {
            $db->query("DELETE FROM accounts WHERE id = '{$account_id}'");
        }
        $db->commit();
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    // test view only

    public function testViewOnly()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'developer' => 'no',
            'create' => 'no',
            'list' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view'));

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }


    // test view + list only

    public function testListOnly()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'developer' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list'));

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test view + list owner
    public function testViewListOwner()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'developer' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'list', 'view'), array('list', 'view'));

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }


 
    // test view owner + edit owner + create
    public function testViewEditOwnerCreate()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'developer' => 'no',
            'list' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'edit', 'view'), array('edit', 'view'));

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test all access, but admin
    public function testAllButAdmin()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'developer' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit', 'delete', 'import', 'export', 'massupdate'));

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test field level
    // test read only all fields
    // test read only 1 field
    public function testReadOnlyOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'fields' =>
            array(
                'website' => array(
                    'write' => 'no',
                    'create' => 'no',
                ),
            ),
            'admin' => 'no',
            'developer' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'website', 50);

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }    

    // test owner write 1 field
    public function testReadOwnerWriteOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'fields' =>
            array(),
            'admin' => 'no',
            'developer' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'website', 60);

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }       

    // test owner read/owner write 1 field
    public function testOwnerReadOwnerWriteOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'fields' =>
            array(),
            'admin' => 'no',
            'developer' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        SugarTestACLUtilities::createField($role->id, 'Accounts','website', 40);

        SugarTestACLUtilities::setupUser($role);

        $mm = MetaDataManager::getManager();
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }
}
