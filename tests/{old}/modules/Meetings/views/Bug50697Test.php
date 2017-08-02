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
 * Bug50697Test.php
 * This test checks the alterations made to modules/Meetings/views/view.listbytype.php to remove the hard-coded
 * UTC_TIMESTAMP function that was used which appears to be MYSQL specific.  Changed to use timedate code instead
 *
 */
class Bug50697Test extends Sugar_PHPUnit_Framework_TestCase
{

public function setUp()
{
    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    unset($GLOBALS['current_user']);
}

/**
 * testProcessSearchForm
 *
 * Test the processSearchForm function which contained the offensive SQL
 */
public function testProcessSearchForm()
{
    global $timedate;
    $_REQUEST = array();
    $mlv = new MeetingsViewListbytype();
    $mlv->processSearchForm();
    $this->assertRegExp('/meetings\.date_start.*?\d{4}-\d{2}-\d{2} \d{1,2}:\d{2}:\d{2}/', $mlv->where, 'Failed to create datetime query for meetings.date_start');

    $_REQUEST['name_basic'] = 'Bug50697Test';
    $mlv->processSearchForm();
    $this->assertRegExp('/meetings\.date_start.*?\d{4}-\d{2}-\d{2} \d{1,2}:\d{2}:\d{2}/', $mlv->where, 'Failed to create datetime query for meetings.date_start');
    $this->assertRegExp('/meetings\.name LIKE \'Bug50697Test%\'/', $mlv->where, 'Failed to generate meetings.name search parameter');
}


}