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
 * Bug 40450 - Extra 'Name' field in a File type module in module builder
 */
require_once 'modules/ModuleBuilder/MB/MBModule.php';

class Bug40450Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $MBModule;
    
    public function setUp()
	{
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->MBModule = new MBModule('testModule', 'custom/modulebuilder/packages/testPkg', 'testPkg', 'testPkg');
	}
	
	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($GLOBALS['current_user']);
        $this->MBModule->delete();
	}
    
    public function testFileModuleNameField()
    {
        $this->MBModule->mbvardefs->mergeVardefs();
        $this->assertArrayHasKey('name', $this->MBModule->mbvardefs->vardefs['fields']);
        $this->MBModule->mbvardefs->templates['file'] = 1;
        $this->MBModule->mbvardefs->mergeVardefs();
        $this->assertArrayNotHasKey('name', $this->MBModule->mbvardefs->vardefs['fields']);
    }
}