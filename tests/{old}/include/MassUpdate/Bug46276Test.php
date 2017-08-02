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
 


class Bug46276Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $opportunities;

	public function setUp()
	{
        // in case someone wipes out these globals.
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');

		global $current_user;
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

		// Create Anon User setted on GMT+1 TimeZone
		$current_user = SugarTestUserUtilities::createAnonymousUser();
		$current_user->setPreference('datef', "Y-m-d");
		$current_user->setPreference('timef', "H:i:s");
		$current_user->setPreference('timezone', "Europe/London");

		// new object to avoid TZ caching
		$timedate = new TimeDate();

		$this->opportunities = new Opportunity();
		$this->opportunities->name = 'Bug46276 Opportunity';
		$this->opportunities->amount = 1234;
		$this->opportunities->sales_stage = "Prospecting";
		$this->opportunities->account_name = "A.G. Parr PLC";
		$this->opportunities->date_closed = '2011-08-12';
		$this->opportunities->save();
	}

	public function tearDown()
	{
		 
		$GLOBALS['db']->query('DELETE FROM opportunities WHERE id = \'' . $this->opportunities->id . '\' ');
		unset($this->opportunities);
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
	}

	//testing handleMassUpdate() for date fields when time zone of the current user is GMT+

	public function testhandleMassUpdateForDateFieldsInGMTPlusTimeZone()
	{
        $this->markTestIncomplete("date_closed on Opportunities is now a formula on enterprise.");
		global $current_user, $timedate;
		$_REQUEST = $_POST = array("module" => "Opportunities",
                                   "action" => "MassUpdate",
                                   "return_action" => "index",
                                   "delete" => "false",
    							   "massupdate" => "true",
    							   "lvso" => "asc",
    							   "uid" => $this->opportunities->id,
    							   "date_closed" => "2011-08-09",		
		);



		$mass = new MassUpdate();
		$mass->setSugarBean($this->opportunities);
		$mass->handleMassUpdate();
		$expected_date = $_REQUEST['date_closed'];
		$actual_date = $this->opportunities->date_closed;
		$this->assertEquals($expected_date, $actual_date);
	}
	 

}
