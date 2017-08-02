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


class Bug45187Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $mod_strings;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Leads');

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
    }
    
    /**
    * @group bug45187
    */
    public function testActivityModuleLabel()
    {
        $this->markTestIncomplete('Opportunities amount is now a calculated field and we have notice on the amount field - To be fixed by MAR/SFA team');
        global $sugar_config;
        global $app_list_strings;

        // init
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();

        // simulate module renaming
        $org_name = $app_list_strings['moduleListSingular']['Contacts'];
        $app_list_strings['moduleListSingular']['Contacts'] = 'People';

        // set the request/post parameters before converting the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        unset($_REQUEST['handle']);
        $_REQUEST['selectedAccount'] = $account->id;
        $sugar_config['lead_conv_activity_opt'] = 'move';

        // call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->init($lead);
        $vc->display();

        // the activity options dropdown should use the renamed module label
        $this->expectOutputRegex('/People<\/OPTION>/');

        // cleanup
        $app_list_strings['moduleListSingular']['Contacts'] = $org_name;
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['record']);
        unset($_REQUEST['selectedAccount']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

    /**
    * @group bug45187
    */
    public function testStudioModuleLabel()
    {
        global $app_list_strings;

        // simulate module renaming
        $org_name = $app_list_strings['moduleList']['Accounts'];
        $app_list_strings['moduleList']['Contacts'] = 'PeopleXYZ';

        // set the request/post parameters
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'Editconvert';

        // call display to generate output
        $vc = new ViewEditConvert();
        $vc->display();

        // ensure the new module name is used
        $this->expectOutputRegex('/.*PeopleXYZ.*/');

        // cleanup
        $app_list_strings['moduleList']['Contacts'] = $org_name;
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
    }
}
