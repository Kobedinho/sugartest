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

class RestBug57835Test extends RestTestPortalBase
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

        if ( isset($this->testapiuser->id) ) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '".$this->testapiuser->id."'");
            if ($GLOBALS['db']->tableExists('users_cstm')) {
                $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c = '".$this->testapiuser->id."'");
            }
        }
        $GLOBALS['db']->commit();
    }

    /**
     * @group rest
     */
    public function testBug57835()
    {
        // Create a portal API user
        $this->testapiuser = BeanFactory::newBean('Users');
        $this->testapiuser->id = "UNIT-TEST-apiuser";
        $this->testapiuser->new_with_id = true;
        $this->testapiuser->first_name = "Portal";
        $this->testapiuser->last_name = "Apiuserson";
        $this->testapiuser->username = "_unittest_apiuser";
        $this->testapiuser->portal_only = true;
        $this->testapiuser->status = 'Active';
        $this->testapiuser->save();
        // unset the default configsetting for the portal user
        $GLOBALS ['system_config']->saveSetting('supportPortal', 'RegCreatedBy', '');
        $GLOBALS['db']->commit();

        $args = array(
            'grant_type' => 'password',
            'username' => 'unittestportal',
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        );
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertEquals('portal_not_configured',$reply['reply']['error']);
                                                          
    }
}
