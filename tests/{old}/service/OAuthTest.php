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

require_once('tests/{old}/service/APIv3Helper.php');

class OAuthTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $_user;
    protected static $_consumer;
    protected $_admin_user;
    protected $_lastRawResponse;

    private static $helperObject;

    protected $aclRole;
    protected $aclField;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("app_strings");
        //Create an anonymous user for login purposes/
        self::$_user = SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp("mod_strings", array('Accounts'));

        self::$helperObject = new APIv3Helper();
        // create our own customer key
        $GLOBALS['db']->query("DELETE FROM oauth_consumer where c_key='TESTCUSTOMER'");
	    $GLOBALS['db']->query("DELETE FROM oauth_nonce where conskey='TESTCUSTOMER'");
        self::$_consumer = new OAuthKey();
	    self::$_consumer->c_key = "TESTCUSTOMER";
        self::$_consumer->c_secret = "TESTSECRET";
        self::$_consumer->save();
    }

    public static function tearDownAfterClass()
	{
        SugarTestHelper::tearDown();
	    $GLOBALS['db']->query("DELETE FROM oauth_consumer where c_key='TESTCUSTOMER'");
	    $GLOBALS['db']->query("DELETE FROM oauth_nonce where conskey='TESTCUSTOMER'");
	    $GLOBALS['db']->query("DELETE FROM oauth_tokens where consumer='".self::$_consumer->id."'");
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}

	public function setUp()
	{
	    if(!SugarOAuthServer::enabled() || !extension_loaded('oauth')) {
            $this->markTestSkipped("No OAuth support");
        }
        $this->oauth = new OAuth('TESTCUSTOMER','TESTSECRET',OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        $this->url = rtrim($GLOBALS['sugar_config']['site_url'],'/').'/service/v4/rest.php';
	    $GLOBALS['current_user'] = self::$_user;
	}

    protected function _returnLastRawResponse()
    {
        return "Error in web services call. Response was: {$this->_lastRawResponse}";
    }

    public function testOauthRequestToken()
    {
        $request_token_info = $this->oauth->getRequestToken($this->url."?method=oauth_request_token");
        $this->assertEquals(rtrim($GLOBALS['sugar_config']['site_url'],'/').'/index.php?module=OAuthTokens&action=authorize', $request_token_info["authorize_url"]);
        $this->assertEquals("true", $request_token_info["oauth_callback_confirmed"]);
        $this->assertNotEmpty($request_token_info['oauth_token']);
        $this->assertNotEmpty($request_token_info['oauth_token_secret']);
        $rtoken = OAuthToken::load($request_token_info['oauth_token']);
        $this->assertInstanceOf('OAuthToken', $rtoken);
        $this->assertEquals(OAuthToken::REQUEST, $rtoken->tstate);
    }

    public function testOauthAccessToken()
    {
        global $current_user;
        $request_token_info = $this->oauth->getRequestToken($this->url."?method=oauth_request_token");
        $this->assertNotEmpty($request_token_info['oauth_token']);
        $this->assertNotEmpty($request_token_info['oauth_token_secret']);
        $token = $request_token_info['oauth_token'];
        $secret = $request_token_info['oauth_token_secret'];

        $c_token = OAuthToken::load($token);
        $this->assertInstanceOf('OAuthToken', $c_token);
        // check token is in the right state
        $this->assertEquals(OAuthToken::REQUEST, $c_token->tstate, "Request token has wrong state");
        $verify = $c_token->authorize(array("user" => $current_user->id));

        $this->oauth->setToken($token, $secret);
        $access_token_info = $this->oauth->getAccessToken($this->url."?method=oauth_access_token&oauth_verifier=$verify");
        $this->assertNotEmpty($access_token_info['oauth_token']);
        $this->assertNotEmpty($access_token_info['oauth_token_secret']);

        $atoken = OAuthToken::load($access_token_info['oauth_token']);
        $this->assertInstanceOf('OAuthToken', $atoken);
        $this->assertEquals($current_user->id, $atoken->assigned_user_id);
        // check this is an access token
        $this->assertEquals(OAuthToken::ACCESS, $atoken->tstate, "Access token has wrong state");
        // check old token was invalidated
        $rtoken = OAuthToken::load($token);
        $this->assertInstanceOf('OAuthToken', $rtoken);
        $this->assertEquals(OAuthToken::INVALID, $rtoken->tstate, "Request token was not invalidated");
    }

    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v4/rest.php';
        // Open a curl session for making the call
        $curl = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        // build the request URL
        $json = json_encode($parameters);
        $postArgs = "method=$method&input_type=JSON&response_type=JSON&rest_data=$json";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        // Make the REST call, returning the result
        $response = curl_exec($curl);
        // Close the connection
        curl_close($curl);

        $this->_lastRawResponse = $response;

        // Convert the result from JSON format to a PHP array
        return json_decode($response,true);
    }

    public function testOauthServiceAccess()
    {
        global $current_user;
        $request_token_info = $this->oauth->getRequestToken($this->url."?method=oauth_request_token");
        $token = $request_token_info['oauth_token'];
        $secret = $request_token_info['oauth_token_secret'];

        $c_token = OAuthToken::load($token);
        $verify = $c_token->authorize(array("user" => $current_user->id));

        $this->oauth->setToken($token, $secret);
        $access_token_info = $this->oauth->getAccessToken($this->url."?method=oauth_access_token&oauth_verifier=$verify");
        $token = $access_token_info['oauth_token'];
        $secret = $access_token_info['oauth_token_secret'];
        $this->oauth->setToken($token, $secret);

        $res = $this->oauth->fetch($this->url."?method=oauth_access&input_type=JSON&response_type=JSON");
        $this->assertTrue($res);
        $session = json_decode($this->oauth->getLastResponse(), true);
        $this->assertNotEmpty($session["id"]);

        // test fetch through OAuth
        $res = $this->oauth->fetch($this->url."?method=get_user_id&input_type=JSON&response_type=JSON");
        $this->assertTrue($res);
        $id = json_decode($this->oauth->getLastResponse(), true);
        $this->assertEquals($current_user->id, $id);
        // test fetch through session initiated by OAuth
        $id2 = $this->_makeRESTCall('get_user_id', array("session" => $session["id"]));
        $this->assertEquals($current_user->id, $id2);
    }
}