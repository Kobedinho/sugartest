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

class Bug51980Test extends Sugar_PHPUnit_Framework_TestCase {
// class Bug51980Test extends  Sugar_PHPUnit_Framework_TestCase{
    private $user;
    private $opp;

	public function setUp()
    {
        $this->markTestIncomplete("Disabling after discussing with Eddy.  Eddy will take a look at why this is breaking Stack 66 build");
        //create user
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->default_team_name = 'global';
        $this->user->is_admin = 1;
        $this->user->save();
        $this->user->retrieve($this->user->id);
        $GLOBALS['current_user'] = $this->user;
        //set some global values that will help with the view
        $_REQUEST['action'] = $GLOBALS['action'] = 'DetailView';
        $_REQUEST['module'] = $GLOBALS['module'] = 'Opportunities';

        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], "Opportunities");


        //create opportunity
        $name = 'Test_51980_'.time();
        $this->opp = new Opportunity();
        $this->opp->name = $name;
        $this->opp->amount = '1000000';
        $this->opp->account_id = '1';
        $this->opp->team_id = '1';
        $this->opp->currency_id = -99;
        $this->opp->save();

	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'Test_51980_%'");
        unset($this->user);
        unset($this->opp);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_strings']);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($GLOBALS['current_user']);
    }

     /**

     */
	public function testDateProperUserFormat()
	{
        //manipulate the date on the bean AFTER it's been created, making sure it is
        //a non standard date format.  We are NOT saving, we just want to mess up the UI presentation.
        $closedate = '2014/12/23';
        $this->opp->date_closed = $closedate;

        //create the view and display opportunity
		$ovd = new OpportunitiesViewDetail();
        $ovd->bean = $this->opp;
        $ovd->action = 'DetailView';
        $ovd->module = 'Opportunities';
        $ovd->type = 'detail';
        $ovd->init($this->opp);
        $ovd->preDisplay();
        $ovd->display();

        //grab the value of what the properly formatted date of the string we injected should be.  Note that this calls the
        //timedate function twice, once to grab the user format, and once to create the string
        $formatted_date = $GLOBALS['timedate']->asUserDate($GLOBALS['timedate']->fromString($closedate, $this->user), $this->user);
        //escape the characters so we can use as a regex.  turn '/' into '\/'
        $formatted_date = str_replace('/','\\/',$formatted_date);
        // lets make sure the date shows up properly formatted in the detail view output.
        $this->expectOutputRegex("/>$formatted_date</");

    }
}