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
require_once('tests/{old}/rest/RestTestPortalBase.php');

class RestPortalLoginTest extends RestTestPortalBase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    /**
     * @group rest
     */
    public function testRestLoginSupportPortal()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        // if ( empty($reply['reply']['access_token']) ) { print_r($reply); }
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
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
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
}