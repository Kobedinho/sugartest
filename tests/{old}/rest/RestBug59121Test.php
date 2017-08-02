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

require_once 'tests/{old}/rest/RestTestBase.php';

class RestBug59121Test extends RestTestBase
{
    protected $_backedUp = false;
    protected $_customFile = 'custom/include/MVC/Controller/wireless_module_registry.php';
    protected $_request = array();

    public function setUp()
    {
        parent::setUp();
        
        // User needs to be an admin user
        $this->_user->is_admin = 1;
        $this->_user->save();
        
        // Check for an existing custom mobile file. If found, remove it. Hard.
        if (file_exists($this->_customFile)) {
            $this->_backedUp = true;
            rename($this->_customFile, $this->_customFile . '.backup');
            
            // Remove it from the autoloader as well
            SugarAutoLoader::delFromMap($this->_customFile);
        }
        
        $this->_request = $_REQUEST;
        
        $this->_clearMetadataCache();
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
        
        parent::tearDown();
    }

    /**
     * @group rest
     * @group Bug59121
     */
    public function testEnablingMobileModulesClearsMetadataCache()
    {
        // Force a mobile platform login since that's what we are testing
        $this->_restLogin($this->_user->user_name, $this->_user->user_name, 'mobile');
        
        // First test... no Documents module in the metadata request
        $reply = $this->_restCall('metadata?type_filter=modules');
        $this->assertArrayHasKey('modules', $reply['reply'], 'The modules list was not found in the response');
        $this->assertArrayNotHasKey('Documents', $reply['reply']['modules'], "Documents was found in the mobile modules array and it should not have been");
        
        // Now add the Documents module to the list
        $_REQUEST['enabled_modules'] = "Accounts,Documents,Contacts,Leads,Opportunities,Cases,Calls,Tasks,Meetings,Employees,Reports,Users";
        $admin = new AdministrationController();
        
        // Capturing the output since that could affect the suite
        ob_start();
        $admin->action_updatewirelessenabledmodules();
        $out = ob_get_clean();
        
        // Now test to make sure it is there
        $reply = $this->_restCall('metadata?type_filter=modules');
        $this->assertArrayHasKey('modules', $reply['reply'], 'The modules list was not found in the response for the second request');
        $this->assertArrayHasKey('Documents', $reply['reply']['modules'], "Documents was NOT found in the mobile modules array and it should have been");
    }
}
