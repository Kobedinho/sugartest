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

require_once 'modules/Administration/updater_utils.php';

class Bug38912 extends Sugar_PHPUnit_Framework_TestCase
{
	/**
	 * Test whitelist of modules and actions 
	 * @var array
	 */
	private $_whiteList;

	private $_state						= 'LICENSE_KEY';

	private $_whiteListModuleAllActions	= 'SomeWhiteListModuleAllActions';
	private $_whiteListModule			= 'SomeWhiteListModule';
	private $_whiteListAction			= 'SomeWhiteListAction';
	private $_nonWhiteListModule		= 'SomeNonWhiteListModule';
	private $_nonWhiteListAction		= 'SomeNonWhiteListAction';


	public function setUp()
	{
		// read format in function getModuleWhiteListForLicenseCheck() description
		$this->_whiteList		= array(
			$this->_whiteListModule				=> array($this->_whiteListAction),
			$this->_whiteListModuleAllActions	=> 'all'
		);
	}

    public function testUserNeedsRedirectModuleNotInWhiteListNoAction()
    {
		$this->assertTrue(
			isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_nonWhiteListModule,
					null, $this->_whiteList),
			"Assert that we need redirect for User on module not in whitelist");
	}
	
	public function testUserNeedsRedirectModuleNotInWhiteListActionNotInWhiteList()
	{
		$this->assertTrue(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_nonWhiteListModule,
						$this->_nonWhiteListAction, $this->_whiteList),
				"Assert that we need redirect for User on module and action not in whitelist");
	}

	public function testUserNeedsRedirectModuleInWhiteListActionNotInWhiteList()
	{
		$this->assertTrue(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_whiteListModule,
						$this->_nonWhiteListAction, $this->_whiteList),
				"Assert that we need redirect for User on module in whitelist and action not in whitelist");
	}

	public function testUserDontNeedRedirectModuleInWhiteListActionInWhiteList()
	{
		$this->assertFalse(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_whiteListModule,
						$this->_whiteListAction, $this->_whiteList),
				"Assert that we dont need redirect for User on module in whitelist and action in whitelist");
	}

	public function testUserDontNeedRedirectModuleInWhiteListForAllActions()
	{
		$this->assertFalse(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_whiteListModuleAllActions,
						$this->_nonWhiteListAction, $this->_whiteList),
				"Assert that we dont need redirect for User on module in whitelist for all actions");
	}


}
