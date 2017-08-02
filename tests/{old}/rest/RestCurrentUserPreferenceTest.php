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

class RestCurrentUserPreferenceTest extends RestTestBase {

    protected static $testPreferences = array(
        'hello' => 'world',
        'test' => 'preference'
    );

    public function setUp()
    {
        parent::setUp();

        // add a test preference to the user
        foreach(self::$testPreferences as $key => $pref) {
            $this->_user->setPreference($key, $pref);
        }

        // save the preferences to the db.
        $this->_user->savePreferencesToDB();
    }
    
    public function tearDown()
    {
        // clean up the preferences
        $this->_user->resetPreferences();

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testGetUserPreferences()
    {
        $reply = $this->_restCall("me/preferences");

        // assert that the reply has our two preferences in it
        $this->assertEquals('world', $reply['reply']['hello']);
        $this->assertEquals('preference', $reply['reply']['test']);
    }

    /**
     * @dataProvider dataProviderGetSpecificPreference
     * @group rest
     *
     * @param $key
     * @param $value
     */
    public function testGetSpecificPreference($key, $value)
    {
        $reply = $this->_restCall('me/preference/' . $key);

        $this->assertEquals($value, $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testUpdatePreferenceReturnsNewKeyValuePair()
    {
        $reply = $this->_restCall('me/preference/hello',  json_encode(array('value' => 'world1')), 'PUT');

        $this->assertEquals(array('hello' => 'world1'), $reply['reply']);
    }

    public function testUpdateMultiplePreferencesReturnsUpdatedKeyValuePair()
    {
        $reply = $this->_restCall('me/preferences',
            json_encode(array('hello' => 'world1', 'test' => 'preference1'))
            , 'PUT');

        $this->assertEquals(array('hello' => 'world1', 'test' => 'preference1'), $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testUpdatePreferenceWithSpecificMetaName()
    {
        $reply = $this->_restCall('me/preferences',
            json_encode(array('datepref' => '(y)(m)(d)', 'timepref' => '(h)(i)'))
            , 'PUT');

        $this->assertEquals('(y)(m)(d)', $reply['reply']['datepref']);
        $this->assertEquals('(h)(i)', $reply['reply']['timepref']);

        // check updated preferences in current user object
        $reply = $this->_restCall('me');
        $this->assertEquals('(y)(m)(d)', $reply['reply']['current_user']['preferences']['datepref']);
        $this->assertEquals('(h)(i)', $reply['reply']['current_user']['preferences']['timepref']);
    }

    /**
     * @group rest
     */
    public function testCreatePreferenceReturnsCreatedKeyValuePair()
    {
        $reply = $this->_restCall('me/preference/create', json_encode(array('value' => 'preference')), 'POST');

        $this->assertEquals(array('create' => 'preference'), $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testDeletePreferenceReturnsDeletedKey()
    {
        $reply = $this->_restCall('me/preference/hello', json_encode(array('value' => 'preference')), 'DELETE');

        $this->assertEquals('hello', $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testGetNonExistantPreferenceReturnsEmptyValue()
    {
        $reply = $this->_restCall('me/preference/this_pref_does_not_exist');

        $this->assertSame("", $reply['reply']);
    }


    public static function dataProviderGetSpecificPreference() {
        $return = array();
        foreach(self::$testPreferences as $key => $pref) {
            $return[] = array($key, $pref);
        }

        return $return;
    }
    
}
