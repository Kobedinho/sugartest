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

class RestLoginTest extends RestTestBase
{
    public function setUp()
    {
        // Start out with a fake auth token to prevent _restCall from auto logging in
        $this->authToken = 'LOGGING_IN';
        
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE id LIKE 'UNIT%'");
        $GLOBALS['db']->query("DELETE FROM oauth_tokens WHERE consumer LIKE '_unit_%'");
        if ( isset($this->contact->id) ) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '".$this->contact->id."'");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c = '".$this->contact->id."'");
            }
        }
        if ( isset($this->apiuser->id) ) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '".$this->apiuser->id."'");
            if ($GLOBALS['db']->tableExists('users_cstm')) {
                $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c = '".$this->apiuser->id."'");
            }
        }
        $GLOBALS ['system_config']->saveSetting('supportPortal', 'RegCreatedBy', '');
        $GLOBALS ['system_config']->saveSetting('portal', 'on', 0);
        $GLOBALS['db']->commit();
    }

    /**
     * @group rest
     */
    public function testRestLoginUser()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        );

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }

    /**
     * @group rest
     */
    public function testRestLoginUserInvalidGrant()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        );

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = 'this-is-not-a-token';
        $replyPing = $this->_restCall('ping');

        $this->assertEquals($replyPing['reply']['error'], 'invalid_grant');
    }

    /**
     * @group rest
     */
    public function testRestOauthViaGet()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        );

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);

        $this->authToken = 'LOGGING_IN';
        $replyPing2 = $this->_restCall('ping?oauth_token='.$reply['reply']['access_token']);
        $this->assertEquals('pong',$replyPing2['reply']);

    }


    /**
     * @group rest
     */
    public function testRestLoginUserAutocreateKey()
    {
        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE c_key = 'sugar'");
        $GLOBALS['db']->commit();
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }


    /**
     * @group rest
     */
    public function testRestLoginCustomIdUser()
    {
        // Create a unit test login ID
        $consumer = BeanFactory::newBean('OAuthKeys');
        $consumer->id = 'UNIT-TEST-regularlogin';
        $consumer->new_with_id = true;
        $consumer->c_key = '_unit_regularlogin';
        $consumer->c_secret = '';
        $consumer->oauth_type = 'oauth2';
        $consumer->client_type = 'user';
        $consumer->save();
        
        $GLOBALS['db']->commit();
        
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => $consumer->c_key,
            'client_secret' => '',
            'platform' => 'base',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }

    /**
     * @group rest
     */
    public function testRestLoginRefresh()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);

        $refreshToken = $reply['reply']['refresh_token'];

        
        $args = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        );
        
        // Prevents _restCall from automatically logging in
        $this->authToken = 'LOGGING_IN';
        $reply2 = $this->_restCall('oauth2/token',json_encode($args));
        // if ( empty($reply2['reply']['access_token']) ) { print_r($reply2); }
        $this->assertNotEmpty($reply2['reply']['access_token']);
        $this->assertNotEmpty($reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply2['reply']['access_token'],$reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply2['reply']['access_token']);
        $this->assertNotEquals($reply['reply']['refresh_token'],$reply2['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply2['reply']['token_type']);
        
        $this->authToken = $reply2['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }

    /**
     * @group rest
     */
    function testLoginFromRegularSession() {
        $this->markTestSkipped("This is throwing PHP warnings. This test needs to be refactored.");
        
        // Dirty, dirty hack to make this pass without php squawking
        //$er = error_reporting();
        //error_reporting($er & ~E_WARNING);
        
        // Kill the session
        session_regenerate_id();
        session_start();

        // We have the technology, we can rebuild it
        $_SESSION = array();
        $_SESSION['is_valid_session'] = true;
        $_SESSION['ip_address'] = '127.0.0.1';
        $_SESSION['user_id'] = $this->_user->id;
        $_SESSION['type'] = 'user';
        $_SESSION['authenticated_user_id'] = $this->_user->id;
        $_SESSION['unique_key'] = $GLOBALS['sugar_config']['unique_key'];
        
        $generatedSession = session_id();
        session_write_close();

        // Try using a normal session as the oauth_token
        $this->authToken = $generatedSession;
        
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);

        // Now try passing the oauth_token in as a GET variable
        $this->authToken = 'LOGGING_IN';
        $replyPing = $this->_restCall('ping?oauth_token='.$generatedSession);
        $this->assertEquals('pong',$replyPing['reply']);
        
    }

    /**
     * @group rest
     */
    function testBadLogin() {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => 'this is not the correct password',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        );

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['error']);
        $this->assertEquals('need_login',$reply['reply']['error']);
    }


}
