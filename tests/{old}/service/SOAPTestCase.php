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

require_once('vendor/nusoap//nusoap.php');

abstract class SOAPTestCase extends Sugar_PHPUnit_Framework_TestCase
{
	public static $_user = null;
	public $_soapClient = null;
	public $_session = null;
	public $_sessionId = '';
    public $_soapURL = '';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$_user = SugarTestUserUtilities::createAnonymousUser();
        self::$_user->status = 'Active';
        self::$_user->is_admin = 1;
        self::$_user->save();
        $GLOBALS['db']->commit();
        $GLOBALS['current_user'] = self::$_user;
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['current_user']);
        $GLOBALS['db']->commit();
        parent::tearDownAfterClass();
    }

    /**
     * Create test user
     *
     */
	public function setUp()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");

        $this->_soapClient = new nusoapclient($this->_soapURL,false,false,false,false,false,600,600);
        parent::setUp();
        $GLOBALS['db']->commit();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown()
    {
        $this->_sessionId = '';

        SugarTestHelper::tearDown();
        $GLOBALS['db']->commit();
    }

    protected function _login()
    {
        $GLOBALS['db']->commit();
    	$result = $this->_soapClient->call('login',
            array('user_auth' =>
                array('user_name' => self::$_user->user_name,
                    'password' => self::$_user->user_hash,
                    'version' => '.01'),
                'application_name' => 'SoapTest', "name_value_list" => array())
            );
        $this->_sessionId = $result['id'];
		return $result;
    }

    /**
     * Create a test user
     *
     */
	public function _setupTestUser() {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['db']->commit();
        $GLOBALS['current_user'] = $this->_user;
    }

    /**
     * Remove user created for test
     *
     */
	public function _tearDownTestUser() {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
       unset($GLOBALS['current_user']);
    }

}
