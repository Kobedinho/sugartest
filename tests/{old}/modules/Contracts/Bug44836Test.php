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



class Bug44836Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
	    $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");
	}

	public function tearDown()
	{
        SugarTestHelper::tearDown();
	}

	public function testContractsSubpanelQuickCreate()
	{
        $_REQUEST['action'] = 'QuickCreate';
        $_REQUEST['target_action'] = $_REQUEST['action'];
		 $subpanelQuickCreate = new SubpanelQuickCreate('Contracts', 'QuickCreate');
		 $this->expectOutputRegex('/check_form\s*?\(\s*?\'form_SubpanelQuickCreate_Contracts\'\s*?\)/');
	}

}
