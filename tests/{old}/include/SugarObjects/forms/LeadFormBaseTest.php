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
 * LeadFormBaseTest.php
 *
 */


class LeadFormBaseTest extends Sugar_PHPUnit_Framework_TestCase {

var $form;
var $lead1;

public function setup()
{
    $GLOBALS['db']->query("DELETE FROM leads WHERE first_name = 'Mike' AND last_name = 'TheSituationSorrentino'");
    $this->form = new LeadFormBase();
    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Leads');

    //Create a test Lead
    $this->lead1 = SugarTestLeadUtilities::createLead();
    $this->lead1->first_name = 'Collin';
    $this->lead1->last_name = 'Lee';
    $this->lead1->save();
    $this->lead1->emailAddress->addAddress('clee@sugarcrm.com', true, false);
    $this->lead1->emailAddress->save($this->lead1->id, $this->lead1->module_dir);
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    SugarTestLeadUtilities::removeAllCreatedLeads();
    SugarTestLeadUtilities::removeCreatedLeadsEmailAddresses();
    unset($this->form);
    unset($this->lead1);
}


/**
 * leadsProvider
 *
 */
public function leadsProvider()
{
    return array(
        array('Collin', 'Lee', true),
        array('', 'Lee', true),
        array('Mike', 'TheSituationSorrentino', false)
    );
    
}


/**
 * testCreatingDuplicateLead
 *
 * @dataProvider leadsProvider
 */
public function testCreatingDuplicateLead($first_name, $last_name, $hasDuplicate)
{
    $_POST['first_name'] = $first_name;
    $_POST['last_name'] = $last_name;
    $_POST['Leads0emailAddresss0'] = 'clee@sugarcrm.com';

    $rows = $this->form->checkForDuplicates();

    if($hasDuplicate)
    {
        $this->assertTrue(count($rows) > 0, 'Assert that checkForDuplicates returned matches');
        $this->assertEquals($rows[0]['last_name'], $last_name, 'Assert duplicate row entry last_name is ' . $last_name);
        $output = $this->form->buildTableForm($rows);
        $this->assertRegExp('/\&action\=DetailView\&record/', $output, 'Assert we have the DetailView links to records');
    } else {
        $this->assertTrue(empty($rows), 'Assert that checkForDuplicates returned no matches');
    }
}

}