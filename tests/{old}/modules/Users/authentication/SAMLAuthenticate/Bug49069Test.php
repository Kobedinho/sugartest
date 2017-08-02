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
require_once('tests/{old}/modules/Users/AuthenticateTest.php');

class Bug49069Test extends  Sugar_PHPUnit_Framework_TestCase
{

	public function setUp()
    {
        $GLOBALS['app'] = new SugarApplication();
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$this->sugar_config_old = $GLOBALS['sugar_config'];
    	$_POST['user_name'] = $_REQUEST['user_name'] = 'foo';
    	$_POST['user_password'] = $_REQUEST['user_password'] = 'bar';
    	$_SESSION['authenticated_user_id'] = true;
    	$_SESSION['hasExpiredPassword'] = false;
    	$_SESSION['isMobile'] = null;
        $GLOBALS['sugar_config']['authenticationClass'] = 'SAMLAuthenticate';
        //$this->useOutputBuffering = false;
	}

	public function tearDown()
	{
	    unset($GLOBALS['current_user']);
	    $GLOBALS['sugar_config'] = $this->sugar_config_old;
	    unset($_POST['login_module']);
        unset($_POST['login_action']);
        unset($_POST['login_record']);
        unset($_POST['user_name']);
        unset($_POST['user_password']);
        unset($_SESSION['authenticated_user_id']);
        unset($_SESSION['hasExpiredPassword']);
        unset($_SESSION['isMobile']);
	}

    public function testDefaultUserNamePasswordNotSet()
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        $_POST['action'] = 'Authenticate';
        $_POST['login_module'] = 'foo';
        $_POST['login_action'] = 'bar';
        $_POST['login_record'] = '123';
        unset($_POST['user_name']);
        unset($_POST['user_password']);
        unset($_REQUEST['user_name']);
        unset($_REQUEST['user_password']);
        $authController = new AuthenticationController((!empty($GLOBALS['sugar_config']['authenticationClass'])? $GLOBALS['sugar_config']['authenticationClass'] : 'SugarAuthenticate'));

        $url = '';
        require('modules/Users/Authenticate.php');

        $this->assertEquals(
            'Location: index.php?module=foo&action=bar&record=123',
            $url
            );
    }

    public function testDefaultUserNamePasswordSet()
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        $_POST['action'] = 'Authenticate';
        $_POST['login_module'] = 'foo';
        $_POST['login_action'] = 'bar';
        $_POST['login_record'] = '123';
        $authController = new AuthenticationController((!empty($GLOBALS['sugar_config']['authenticationClass'])? $GLOBALS['sugar_config']['authenticationClass'] : 'SugarAuthenticate'));

        $url = '';
        require('modules/Users/Authenticate.php');

        $this->assertEquals(
            'Location: index.php?module=foo&action=bar&record=123',
            $url
            );

        $this->assertTrue(!empty($_REQUEST['user_name']), 'Assert that we automatically set a user_name in $_REQUEST');
        $this->assertEquals('foo', $_REQUEST['user_name']);
        $this->assertTrue(!empty($_REQUEST['user_password']), 'Assert that we automatically set a user_password in $_REQUEST');
        $this->assertEquals('bar', $_REQUEST['user_password']);
    }
}
?>
