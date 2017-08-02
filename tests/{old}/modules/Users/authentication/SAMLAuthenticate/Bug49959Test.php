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
 * @ticket 49959
 */
class Bug49959Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SAMLAuthenticateUserTest
     */
    protected static $auth;

    /**
     * @var User
     */
    protected static $user;
    protected static $user_id,
        $user_name  = 'Bug49959TestUser',
        $user_email = 'bug49959.test.user@example.com';

    /**
     * This method is called before the first test of this test class is run.
     *
     * Creates shared test resources
     */
    public static function setUpBeforeClass()
    {
        self::$auth = new SAMLAuthenticateUserTest;
        $user = self::$user = BeanFactory::newBean('Users');

        $user->user_name = self::$user_name;
        $user->email1    = self::$user_email;

        self::$user_id = $user->save();
    }


    /**
     * This method is called after the last test of this test class is run.
     *
     * Removes shared test resources
     */
    public static function tearDownAfterClass()
    {
        $GLOBALS['db']->query("DELETE FROM users WHERE user_name='".self::$user_name."'");
    }

    /**
     * Test fetching of a user to be authenticated by different fields
     */
    public function testFetchUser()
    {
        // test fetching user by ID
        $user = self::$auth->fetch_user(self::$user_id, 'id');
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by username
        $user = self::$auth->fetch_user(self::$user_name, 'user_name');
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by email (default case)
        $user = self::$auth->fetch_user(self::$user_email);
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by unsupported field
        $user = self::$auth->fetch_user(self::$user_email, 'unsupported_field');
        $this->assertNull($user->id);

        // test fetching non-existing user
        $user = self::$auth->fetch_user('some_wrong_key');
        $this->assertNull($user->id);
    }

    /**
     * Test that get_nameid() method of OneLogin_Saml_Response is called by default
     */
    public function testDefaultNameId()
    {
        // create a mock of SAML response
        $mock = $this->getResponse();
        $mock->expects($this->once())
            ->method('getNameId');

        // create a default SAML settings object
        self::$auth->settings = SAMLAuthenticate::loadSettings();
        self::$auth->samlresponse = $mock;

        // expect that get_nameid() method of response is used by default
        self::$auth->get_user_id();
    }

    /**
     * Test that custom XPath is used when specified in settings
     */
    public function testCustomNameId()
    {
        $node_id = 'Bug49959Test';

        // create response with custom XML
        $mock2 = $this->getResponse();
        $mock2->xml = $this->getResponseXml($node_id);

        // create SAML settings object with custom name id definition
        self::$auth->settings = $settings = SAMLAuthenticate::loadSettings();
        self::$auth->samlresponse = $mock2;

        $settings->saml2_settings['check']['user_name'] = '//root';
        $settings->useXML = true;
        self::$auth->xpath = new DOMXPath($mock2->xml);

        // expect that user ID is fetched from the document according to settings
        $result = self::$auth->get_user_id();
        $this->assertEquals($node_id, $result);
    }

    /**
     * Returns a mock of SamlResponse object
     *
     * @return OneLogin_Saml_Response
     */
    protected function getResponse()
    {
        return $this->createMock('OneLogin_Saml_Response');
    }

    /**
     * Returns custom response XML document
     *
     * @param $node_id
     * @return DOMDocument
     */
    protected function getResponseXml($node_id)
    {
        $document = new DOMDocument();
        $document->loadXML('<root>' . $node_id . '</root>');
        $root = $document->createElement('root');
        $document->appendChild($root);
        return $document;
    }
}

/**
 * A SAMLAuthenticate class wrapper that makes some of its methods accessible
 */
class SAMLAuthenticateUserTest extends SAMLAuthenticateUser
{
    public $xpath;

    public function fetch_user($id, $field = null)
    {
        return parent::fetch_user($id, $field);
    }

    public function get_user_id()
    {
        return parent::get_user_id();
    }
}
