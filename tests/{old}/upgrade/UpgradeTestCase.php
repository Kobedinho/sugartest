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


abstract class UpgradeTestCase extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TestUpgrader
     */
    protected $upgrader;

    /**
     * admin user
     * @var User
     */
    static protected $admin;

    public static function setUpBeforeClass(){
        // create admin user
        self::$admin = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user'] = static::$admin;
    }

    public static function tearDownAfterClass(){
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function setUp(){
        // make sure current_user is not flushed out by SugarTestHelper::tearDown()
        $GLOBALS['current_user'] = static::$admin;
        $this->upgrader = new TestUpgrader(self::$admin);
        SugarTestHelper::setUp("files");
    }

    protected function tearDown(){
        $this->upgrader->cleanState();
        $this->upgrader->cleanDir($this->upgrader->getTempDir());
        SugarTestHelper::tearDown();
    }
}
