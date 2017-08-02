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
 * @ticket 66034
 */
class Bug66034Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $aclAction;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $old_current_user = $GLOBALS['current_user'];
        $new_current_user = new SugarACLDeveloperOrAdminUserMock();
        $new_current_user->retrieve($old_current_user->id);
        $GLOBALS['current_user'] = $new_current_user;
        $this->aclAction = new ACLAction();
    }

    public function tearDown()
    {
        $this->aclAction->clearSessionCache();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function aclAccessData() 
    {
        return array(
                array('ContractTypes', 'Contracts', 'list', 'Developer', true, 'Developer should be able to access ContractTypes'),
                array('ContractTypes', 'Contracts', 'list', 'Admin', true, 'Admin should be able to access ContractTypes'),
                array('ContractTypes', 'Contracts', 'list', '', false, 'Regular user should not be able to access ContractTypes'),
                array('Releases', 'Bugs', 'edit', 'Developer', true, 'Developer should be able to edit Releases'),
                array('Releases', 'Bugs', 'edit', 'Admin', true, 'Admin should be able to edit Releases'),
                array('Releases', 'Bugs', 'edit', '', false, 'Regular user should not be able to edit Releases'),
                array('ACLRoles', 'Users', 'list', 'Developer', true, 'Developer should be able to list ACLRoles'),
                array('ACLRoles', 'Users', 'list', 'Admin', true, 'Admin should be able to list ACLRoles'),
                array('ACLRoles', 'Users', 'list', '', false, 'Regular user should not be able to list ACLRoles'),
                array('ProductTemplates', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit ProductTemplates'),
                array('ProductTemplates', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit ProductTemplates'),
                array('ProductTemplates', 'Products', 'edit', '', false, 'Regular user should not be able to edit ProductTemplates'),
                array('ProductTypes', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit ProductTypes'),
                array('ProductTypes', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit ProductTypes'),
                array('ProductTypes', 'Products', 'edit', '', false, 'Regular user should not be able to edit ProductTypes'),
                array('ProductCategories', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit ProductCategories'),
                array('ProductCategories', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit ProductCategories'),
                array('ProductCategories', 'Products', 'edit', '', false, 'Regular user should not be able to edit ProductCategories'),
                array('Manufacturers', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit Manufacturers'),
                array('Manufacturers', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit Manufacturers'),
                array('Manufacturers', 'Products', 'edit', '', false, 'Regular user should not be able to edit Manufacturers'),
                array('Shippers', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit Shippers'),
                array('Shippers', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit Shippers'),
                array('Shippers', 'Products', 'edit', '', false, 'Regular user should not be able to edit Shippers'),
                array('TaxRates', 'Quotes', 'edit', 'Developer', true, 'Developer should be able to edit TaxRates'),
                array('TaxRates', 'Quotes', 'edit', 'Admin', true, 'Admin should be able to edit TaxRates'),
                array('TaxRates', 'Quotes', 'edit', '', false, 'Regular user should not be able to edit TaxRates'),
                );
    }

    /**
     * @dataProvider aclAccessData
     */
    public function testAclAccess($module, $aclModule, $action, $role, $result, $message)
    {
        $bean = BeanFactory::newBean($module);

        if (!empty($role)) {
            $method = 'set'.$role.'ForModule';
            $GLOBALS['current_user']->$method($aclModule);
            $this->aclAction->clearSessionCache();
        }
        
        $this->assertEquals($result, $bean->ACLAccess($action), $message);
    }
}

class SugarACLDeveloperOrAdminUserMock extends User
{
    protected $developerForModules = array();
    protected $adminForModules = array();

    public function setDeveloperForModule($module)
    {
        $this->developerForModules[$module] = true;
    }

    public function setAdminForModule($module)
    {
        $this->adminForModules[$module] = true;
    }

    public function isDeveloperForModule($module)
    {
        return !empty($this->developerForModules[$module]);
    }
    
    public function isAdminForModule($module)
    {
        return !empty($this->adminForModules[$module]);
    }
}