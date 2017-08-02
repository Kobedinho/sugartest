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

require_once('tests/{old}/rest/RestTestBase.php');

class RestBug54947Test extends RestTestBase {
    public $createdFiles = array();

    public function tearDown()
    {
        // Cleanup
        foreach($this->createdFiles as $file)
        {
        	if (is_file($file))
        		SugarAutoLoader::unlink($file, true);
        }

        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testModuleNameSingular() {
        $restReply = $this->_restCall('metadata?type_filter=mod_strings&platform=mobile');
        foreach (SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
            require $file;
        }

        // $wireless_module_registry is defined in the file loaded above
        $enabledMobile = array_keys($wireless_module_registry);
        
        foreach($enabledMobile AS $module) {
            if(isset($restReply['reply']['mod_strings'][$module])) {
                $this->assertTrue(array_key_exists('LBL_MODULE_NAME_SINGULAR', $restReply['reply']['mod_strings'][$module]), "{$module} didn't have LBL_MODULE_NAME_SINGULAR it has: " . print_r($restReply['reply']['mod_strings'][$module], true));
            }
        }
    }

}

