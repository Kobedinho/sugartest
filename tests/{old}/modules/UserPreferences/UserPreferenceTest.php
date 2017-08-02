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

class UserPreferenceTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected static $user;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$user = SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        global $current_user;
        $current_user = self::$user;
    }

    public function tearDown()
    {
        $_SESSION = array();
    }

    public function testSettingAUserPreferenceInCache()
    {
        self::$user->setPreference('test_pref', 'dog');

        $cache = SugarCache::instance();
        $key = self::$user->id . '_PREFERENCES';
        $cachedvalue = $cache->$key;

        $this->assertEquals('dog', self::$user->getPreference('test_pref'));
        $this->assertEquals('dog', $cachedvalue['global']['test_pref']);
    }

    public function testGetUserDateTimePreferences()
    {
        $res = self::$user->getUserDateTimePreferences();
        $this->assertArrayHasKey('date', $res);
        $this->assertArrayHasKey('time', $res);
        $this->assertArrayHasKey('userGmt', $res);
        $this->assertArrayHasKey('userGmtOffset', $res);
    }

    public function testUpdateAllUserPrefs()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $bean = new UserPreference();
        $result = $bean->updateAllUserPrefs('test_pref', 'Value');
        $this->assertEmpty($result);
    }

    public function testPreferenceLifeTime()
    {
        $bean = new UserPreference(self::$user);
        $bean->setPreference('test_pref', 'Value2');
        $this->assertEquals('Value2', self::$user->getPreference('test_pref'));
        $bean->removePreference('test_pref');
        $this->assertEmpty(self::$user->getPreference('test_pref'));
    }

    /**
     * @depends testSettingAUserPreferenceInSession
     */
    public function testResetPreferences()
    {
        self::$user->setPreference('reminder_time', 25);
        self::$user->setPreference('test_pref', 'Value3');
        self::$user->resetPreferences();
        $this->assertEquals(1800, self::$user->getPreference('reminder_time'));
        $this->assertEmpty(self::$user->getPreference('test_pref'));
    }
}
