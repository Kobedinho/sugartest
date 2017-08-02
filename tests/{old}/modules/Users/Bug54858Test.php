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
 * @group 54858
 *
 */
class Bug54858Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->email1 = $email = 'test'.uniqid().'@test.com';
        $this->user->save();
        $GLOBALS['current_user'] = $this->user;
        $this->vcal_url =  "{$GLOBALS['sugar_config']['site_url']}/vcal_server.php/type=vfb&source=outlook&email=" . urlencode($email);
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
    	unset($GLOBALS['current_user']);
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Test that new user gets ical key
     */
    public function testCreateNewUser()
    {
        $this->assertNotEmpty($this->user->getPreference('calendar_publish_key'), "Publish key is not set");
    }

	protected function callVcal($key)
	{
       $ch = curl_init($this->vcal_url."&key=" . urlencode($key));
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	   $return = curl_exec($ch);
	   $info = curl_getinfo($ch);
	   $info['return'] = $return;
	   return $info;
	}

	// test vcal service
    public function testPublishKey()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
        $res = $this->callVcal('');
		$this->assertEquals('401', $res['http_code']);

        $res = $this->callVcal('blah');
		$this->assertEquals('401', $res['http_code']);

		$key = $this->user->getPreference('calendar_publish_key');
        $res = $this->callVcal($key);
		$this->assertEquals('200', $res['http_code']);
		$this->assertContains('BEGIN:VCALENDAR', $res['return']);

		// now reset the key
        $this->user->setPreference('calendar_publish_key', '');
        $this->user->savePreferencesToDB();
        $GLOBALS['db']->commit();

        $res = $this->callVcal('');
		$this->assertEquals('401', $res['http_code']);
        $res = $this->callVcal($key);
		$this->assertEquals('401', $res['http_code']);
	}
}
