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
 * Bug 40299 - Editing Stock Fields Causes SQL Errors After Custom Module Is Deployed
 */
require_once 'modules/ModuleBuilder/MB/MBModule.php';

class Bug40299Test extends Sugar_PHPUnit_Framework_TestCase
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
    
    public function testFieldExistForBasicFields()
    {
        $this->assertEquals(true, $this->MBModule->fieldExists('name'));
    }
}