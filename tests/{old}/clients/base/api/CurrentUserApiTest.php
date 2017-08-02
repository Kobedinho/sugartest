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
require_once "tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php";

/**
 * @group ApiTests
 */
class CurrentUserApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $currentUserApiMock;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        OutboundEmailConfigurationTestHelper::setUp();
        // load up the unifiedSearchApi for good times ahead
        $this->currentUserApiMock = new CurrentUserApiMock();
    }

    public function tearDown()
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testCurrentUserLanguage()
    {
        // test from session
        $_SESSION['authenticated_user_language'] = 'en_UK';
        $result = $this->currentUserApiMock->getBasicInfo();
        $this->assertEquals('en_UK', $result['preferences']['language']);
        // test from user
        unset($_SESSION['authenticated_user_language']);
        $GLOBALS['current_user']->preferred_language = 'AWESOME';
        $result = $this->currentUserApiMock->getBasicInfo();
        $this->assertEquals('AWESOME', $result['preferences']['language']);
        // test from default
        unset($_SESSION['authenticated_user_language']);
        unset($GLOBALS['current_user']->preferred_language);
        $result = $this->currentUserApiMock->getBasicInfo();
        $this->assertEquals($GLOBALS['sugar_config']['default_language'], $result['preferences']['language']);
    }
    /**
     * @group wizard
     */
    public function testShowFirstLoginWizard()
    {
        global $current_user;
        $current_user->setPreference('ut', '0');
        $current_user->savePreferencesToDB();
        $result = $this->currentUserApiMock->shouldShowWizard();
        $this->assertTrue($result, "We show Wizard when user's preference 'ut' is falsy");
        $current_user->setPreference('ut', '1');
        $current_user->savePreferencesToDB();
        $result = $this->currentUserApiMock->shouldShowWizard();
        $this->assertFalse($result, "We do NOT show Wizard when user's preference 'ut' is truthy");
    }
}

class CurrentUserApiMock extends CurrentUserApi
{
    public function getBasicInfo()
    {
        return parent::getBasicUserInfo('base');
    }
}
