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
 * Bug49627Test.php
 *
 * This unit test tests the user type dropdown items created from the UserViewHelper class.
 * It runs tests against the normal user, portal and group user types.
 */
class Bug49627Test extends Sugar_PHPUnit_Framework_TestCase
{

var $normalUser;
var $portalUser;
var $groupUser;


public function setUp()
{
    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();

    $this->normalUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->normalUser->id = create_guid();
    $this->normalUser->user_type = 'RegularUser';

    $this->portalUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->portalUser->id = create_guid();
    $this->portalUser->is_portal = 1;
    $this->portalUser->user_type = 'PORTAL_ONLY';

    $this->groupUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->groupUser->id = create_guid();
    $this->groupUser->is_group = 1;
    $this->groupUser->user_type = 'GROUP';

    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
}

public function testSetupUserTypeDropdownNormalUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->normalUser);
    $userViewHelper->usertype = 'RegularUser';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/RegularUser/', $dropdown);
    $this->assertRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);

    $this->normalUser->id = '';
    $userViewHelper = new UserViewHelperMock($smarty, $this->normalUser);
    $userViewHelper->usertype = 'RegularUser';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/RegularUser/', $dropdown);
    $this->assertRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);
}

public function testSetupUserTypeDropdownGroupUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->groupUser);
    $userViewHelper->usertype = 'GROUP';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/GROUP/', $dropdown);
    $this->assertRegExp('/GROUP/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);

    $userViewHelper = new UserViewHelperMock($smarty, $this->groupUser);
    $this->groupUser->id = '';
    $userViewHelper->usertype = 'GROUP';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/GROUP/', $dropdown);
    $this->assertRegExp('/GROUP/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);
}

public function testSetupUserTypeDropdownPortalUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->portalUser);
    $userViewHelper->usertype = 'PORTAL_ONLY';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);

    $this->portalUser->id = '';
    $userViewHelper = new UserViewHelperMock($smarty, $this->portalUser);
    $userViewHelper->usertype = 'PORTAL_ONLY';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);

}

}

//UserViewHelperMock
//This class turns the $ss class variable to have public access
class UserViewHelperMock extends UserViewHelper
{
    var $ss;
}
