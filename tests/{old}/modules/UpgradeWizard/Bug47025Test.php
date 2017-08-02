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

class Bug47025Test extends Sugar_PHPUnit_Framework_TestCase  {

var $user;

public function setUp()
{
    //Set all Users to have deleted to 2 so we only test one user
    $GLOBALS['db']->query("UPDATE users SET deleted = 1, messenger_type = 'Bug47025' WHERE deleted = 0");
    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();
    $current_user->setPreference('user_theme', 'Green', 0, 'global');
    $current_user->setPreference('max_tabs', '10', 0, 'global');
    $current_user->save();
    $this->user = $current_user;

	require('include/modules.php');
	$GLOBALS['beanList'] = $beanList;
	$GLOBALS['beanFiles'] = $beanFiles;
    $_SESSION['upgrade_from_flavor'] = 'SugarCE to SugarPro';
    require_once('modules/UpgradeWizard/uw_utils.php');
}

public function tearDown()
{
	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    if(isset($_SESSION['upgrade_from_flavor']))
    {
       unset($_SESSION['upgrade_from_flavor']);
    }
    $GLOBALS['db']->query("UPDATE users SET deleted = 0, messenger_type = NULL WHERE deleted = 1 AND messenger_type = 'Bug47025'");
}

public function testUpgradeUserPreferencesCeToPro()
{
    $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
    upgradeUserPreferences();
    unset($_SESSION[$this->user->user_name.'_PREFERENCES']['global']);
    $user = new User();
    $user->retrieve($this->user->id);
    $theme = $user->getPreference('user_theme');
    $tabs = (int)$user->getPreference('max_tabs');
    $this->assertEquals('RacerX', $theme, 'Assert that theme is upgraded to RacerX on CE->PRO upgrade');
    $this->assertEquals(10, $tabs, 'Assert that number of tabs is not changed');
}

public function testUpgradeUserPreferencesCeToProWithTabValue()
{
    $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
    $user = new User();
    $user->retrieve($this->user->id);
    $user->setPreference('max_tabs', '0', 0, 'global');
    $user->savePreferencesToDB();
    upgradeUserPreferences();
    unset($_SESSION[$this->user->user_name.'_PREFERENCES']['global']);
    $user->retrieve($this->user->id);
    $theme = $user->getPreference('user_theme');
    $tabs = (int)$user->getPreference('max_tabs');
    $this->assertEquals('RacerX', $theme, 'Assert that theme is upgraded to RacerX on CE->PRO upgrade');
    $this->assertEquals(7, $tabs, 'Assert that number of tabs defaults to 7 if it was empty');
}

public function testUpgradeUserPreferencesNonFlavor()
{
    $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
    unset($_SESSION['upgrade_from_flavor']);
    upgradeUserPreferences();
    unset($_SESSION[$this->user->user_name.'_PREFERENCES']['global']);
    $user = new User();
    $user->retrieve($this->user->id);
    $theme = $user->getPreference('user_theme');
    $tabs = (int)$user->getPreference('max_tabs');
    $this->assertEquals('Green', $theme, 'Assert that theme is not upgraded if not flavor conversion');
    $this->assertEquals(10, $tabs, 'Assert that number of tabs is not changed');
}

}

