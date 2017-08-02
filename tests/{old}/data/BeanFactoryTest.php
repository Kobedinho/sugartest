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
 * @coversDefaultClass BeanFactory
 */
class BeanFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }

        BeanFactory::unsetBeanClass();
    }

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
    public function testGetBean()
    {
        $module = "Accounts";
        global $beanList, $beanFiles;
        require('include/modules.php');

        $account = BeanFactory::newBean($module);
        $account->name = "Unit Test";
        $account->save();
        $this->createdBeans[] = $account;

        $validBean = BeanFactory::retrieveBean($module, $account->id);

        $this->assertEquals($account->id, $validBean->id);

        //Ensure we get a false if we try to load a bad bean.
        $uniqueID = uniqid();
        $invalidBean = BeanFactory::retrieveBean($module, $uniqueID);
        $this->assertFalse(isset($invalidBean->id));
    }

    public function testRegisterBean()
    {
        // Create a new record
        $module = 'Accounts';
        $account = BeanFactory::newBean($module);
        $account->name = 'BeanFactoryTest';
        $account->save();
        $this->createdBeans[] = $account;

        // Test that it is registered
        $registered = BeanFactoryTestMock::isRegistered($account);
        $this->assertTrue($registered, "Newly created Account bean is not registered");

        // Change the record and get it again
        $account->name = 'BeanFactoryTestHASCHANGED';
        $account->save();

        // Test that the changes took
        $new = BeanFactory::getBean($module, $account->id);
        $this->assertEquals($account->name, $new->name);
    }
    
    public function testRegisterBeanLegacyStyle()
    {
        // Create a new record
        $module = 'Accounts';
        $account = BeanFactory::newBean($module);
        $account->name = 'BeanFactoryTest';
        $account->save();
        $this->createdBeans[] = $account;

        // Unregister it so we can test registration
        BeanFactory::unregisterBean($account);
        $unregistered = BeanFactoryTestMock::isRegistered($account);
        $this->assertFalse($unregistered, "New bean is still registered in the factory");
        
        // Test registration old style way
        $registered = BeanFactory::registerBean($module, $account, $account->id);
        $this->assertTrue($registered, "Legacy style registration of the bean failed");
        
        // Double ensure it worked
        $registered = BeanFactoryTestMock::isRegistered($account);
        $this->assertTrue($registered, "Legacy style registration did not actually register the bean");
    }

    public function testUnregisterBean()
    {
        // Create the bean and save to register
        $module = 'Accounts';
        $account = BeanFactory::newBean($module);
        $account->name = 'BeanFactoryTest';
        $account->save();
        $this->createdBeans[] = $account;

        // Test that unregister is true for a bean
        $unregistered = BeanFactory::unregisterBean($account);
        $this->assertTrue($unregistered, "Unregister with a bean failed");

        // Test that the bean is no longer in the registry
        $unregistered = BeanFactoryTestMock::isRegistered($account);
        $this->assertFalse($unregistered, "New bean is still registered in the factory");
    }

    /**
     * Test BeanFactory::getModuleName().
     * @dataProvider providerGetModuleNameByBean
     */
    public function testGetModuleNameByBean($bean, $expectedValue)
    {
        //Test when the function argument is a SugarBean
        $moduleName = BeanFactory::getModuleName($bean);
        $this->assertEquals($moduleName, $expectedValue);
    }

    public function providerGetModuleNameByBean()
    {
        // simulate issue with incorrect module_name property on Filters module
        $filterBean = BeanFactory::newBean("Filters");
        $filterBean->module_name = 'Accounts';

        return array(
            array(
                BeanFactory::newBean("Accounts"),
                "Accounts",
            ),
            array(
                BeanFactory::newBean("Cases"),
                "Cases",
            ),
            array(
                BeanFactory::newBean("Users"),
                "Users",
            ),
            array(
                new stdClass(),
                false,
            ),
            array(
                $filterBean,
                'Filters',
            ),
        );
    }

    /**
     * Test BeanFactory::getModuleName().
     * @dataProvider providerGetModuleNameByName
     */
    public function testGetModuleNameByName($name, $expectedValue)
    {
        //Test when the function argument is a string (object name)
        $moduleName = BeanFactory::getModuleName($name);
        $this->assertEquals($moduleName, $expectedValue);
    }

    public function providerGetModuleNameByName()
    {
        return array(
            array(
                "Account",
                "Accounts",
            ),
            array(
                "Case",
                "Cases",
            ),
            array(
                "User",
                "Users",
            ),
            array(
                "Group",
                false,
            ),
            array(
                "aCase",
                false,
            ),
            array(
                "Cases",
                false,
            ),
        );
    }

    /**
     * @covers ::setBeanClass()
     */
    public function testSetBeanClass()
    {
        $this->assertEquals('Contact', BeanFactory::getObjectName('Contacts'));
        $this->assertEquals('Contact', BeanFactory::getBeanClass('Contacts'));

        BeanFactory::setBeanClass('Contacts', 'MyContact');

        $this->assertEquals('Contact', BeanFactory::getObjectName('Contacts'));
        $this->assertEquals('MyContact', BeanFactory::getBeanClass('Contacts'));

        BeanFactory::unsetBeanClass('Contacts');

        $this->assertEquals('Contact', BeanFactory::getObjectName('Contacts'));
        $this->assertEquals('Contact', BeanFactory::getBeanClass('Contacts'));
    }
}

class BeanFactoryTestMock extends BeanFactory
{
    public static function isRegistered($bean)
    {
        if (!empty($bean->module_name) && !empty($bean->id)) {
            return isset(self::$loadedBeans[$bean->module_name][$bean->id]);
        }

        return false;
    }
}
