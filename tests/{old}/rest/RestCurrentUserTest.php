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

class RestCurrentUserTest extends RestTestBase {
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
    public function testRetrieve() {
        $restReply = $this->_restCall("me");
        $this->assertNotEmpty($restReply['reply']['current_user']['id']);
        $this->assertNotEmpty($restReply['reply']['current_user']['preferences']['currency_id']);
        $this->assertNotEmpty($restReply['reply']['current_user']['preferences']['decimal_precision']);
        $this->assertNotEmpty($restReply['reply']['current_user']['preferences']['language']);
        $this->assertNotEmpty($restReply['reply']['current_user']['preferences']['default_teams']);
        $this->assertNotEmpty($restReply['reply']['current_user']['my_teams']);
    }

    /**
     * @group rest
     */
    public function testRetrieveDefaults()
    {
        // FIXME TY-1313: investigate why this test fails
        global $current_user,$sugar_config;
        $real_current_user = $current_user;
        // The reset preferences call will fail because it's trying to mess with a session
        // unless the "current user" isn't the user we are changing the preferences on.
        $current_user = new User();
        $current_user->id = 'NOT-THE-REAL-THING';
        $real_current_user->resetPreferences();
        $current_user = $real_current_user;

        $restReply = $this->_restCall('me');
        $this->assertEquals($sugar_config['datef'],$restReply['reply']['current_user']['preferences']['datepref'],"trd: Date pref is not the default");
        $this->assertEquals($sugar_config['default_time_format'],$restReply['reply']['current_user']['preferences']['timepref'],"trd: Time pref is not the default");

        $current_user->setPreference('datef','m/d/Y');
        $current_user->setPreference('timef','H:i a');
        $current_user->savePreferencesToDB();
        
        // Need to logout and log back in, preferences are cached in the session.
        $this->_restLogin();
        $restReply = $this->_restCall('me');
        $this->assertEquals('m/d/Y',$restReply['reply']['current_user']['preferences']['datepref'],"trd: Date pref is not the configured value");
        $this->assertEquals('H:i a',$restReply['reply']['current_user']['preferences']['timepref'],"trd: Time pref is not the configured value");
    }
    
    /**
     * @group rest
     */
    public function testAclUsers()
    {
        // FIXME TY-1313: investigate why this test fails
      $restReply = $this->_restCall("me");
      // verify the user is not the admin of the users module
      $userAcl = $restReply['reply']['current_user']['acl']['Users'];
      $this->assertEquals('no', $userAcl['admin'], "This user is the admin and should not be");
      // log in as an admin
      $GLOBALS['current_user']->is_admin = 1;
      $GLOBALS['current_user']->save();
      $restReply = $this->_restCall("me");
      // verify the user is the admin of the users module
      $userAcl = $restReply['reply']['current_user']['acl']['Users'];
      $this->assertEquals('yes', $userAcl['admin'], "This user is not the admin and they should be");
    } 

    /**
     * @group rest
     */
    public function testUpdate()
    {
        // FIXME TY-1313: investigate why this test fails
        $restReply = $this->_restCall("me", json_encode(array('first_name' => 'UNIT TEST - AFTER')), "PUT");
        $this->assertNotEquals(stripos($restReply['reply']['current_user']['full_name'], 'UNIT TEST - AFTER'), false);
    }

    /**
     * @group rest
     */
    public function testPasswordUpdate() {
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'W0nkY123', 'old_password' => $GLOBALS['current_user']->user_name)),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], true);
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'Y3s1tWorks', 'old_password' => 'W0nkY123')),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], true);

        // Incorrect old password returns valid:false
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'Y@ky1234', 'old_password' => 'justwrong!')),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], false);
    }
        
    /**
     * @group rest
     */
    public function testPasswordVerification() {
        $reply = $this->_restCall("me/password",
            json_encode(array('password_to_verify' => $GLOBALS['current_user']->user_name)),
            'POST');
        $this->assertEquals($reply['reply']['valid'], true);
        $reply = $this->_restCall("me/password",
            json_encode(array('password_to_verify' => 'noway')),
            'POST');
        $this->assertEquals($reply['reply']['valid'], false);
    }
    
}
