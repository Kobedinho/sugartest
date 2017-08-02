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

class Bug59196Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_request;
    protected $_customFile = 'custom/include/MVC/Controller/wireless_module_registry.php';
    protected $_backedUp;

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', array(true, true)); // Admin

        // Backup the custom file if there is one
        if (file_exists($this->_customFile)) {
            $this->_backedUp = true;
            rename($this->_customFile, $this->_customFile . '.backup');

            // Remove it from the autoloader as well
            SugarAutoLoader::delFromMap($this->_customFile);
        }

        // Backup the request
        if(!empty($_REQUEST)) {
            $this->_request = $_REQUEST;
        }
    }

    public function tearDown()
    {
        $_REQUEST = $this->_request;

        @unlink($this->_customFile);
        SugarAutoLoader::delFromMap($this->_customFile);

        if ($this->_backedUp) {
            rename($this->_customFile . '.backup', $this->_customFile);
            SugarAutoLoader::addToMap($this->_customFile);
        }

        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug59196
     */
    public function testChangingMobileModuleListMaintainsSelectedOrder()
    {
        // Add Documents module to the list
        $_REQUEST['enabled_modules'] = "Accounts,Documents,Contacts,Leads";
        $admin = new AdministrationController();

        // Capturing the output since that could affect the suite
        ob_start();
        $admin->action_updatewirelessenabledmodules();
        $out = ob_get_clean();

        // Begin assertions
        $this->assertFileExists($this->_customFile, "Custom wireless module registry file was not written");

        include $this->_customFile;

        $this->assertTrue(isset($wireless_module_registry), "Wireless module registry not found in the custom file");
        $this->assertInternalType('array', $wireless_module_registry, "Wireless module registry is not an array");
        $this->assertEquals(4, count($wireless_module_registry), "Expected wireless module registry to contain 4 modules");

        // Grab the keys and compare
        $modules = array_keys($wireless_module_registry);
        $this->assertEquals('Documents', $modules[1], "Second module in wireless module list should be 'Documents'");
    }
}
