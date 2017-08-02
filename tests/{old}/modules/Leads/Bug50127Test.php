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
 * 
 * Test if Contact is properly linked to Lead if we are not creating a contact
 * but linking an existing one.
 * Check if Account is linked with Contact.
 * 
 * @author avucinic@sugarcrm.com
 *
 */
class Bug50127Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }
    
    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        
        SugarTestHelper::tearDown();

        $_REQUEST = array();
    }
 
    /**
     * Create a lead and convert it to an existing Account and Contact
     */
    public function testConvertLinkingExistingContact() {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');

        // Create records
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();

        // ConvertLead to an existing Contact and Account
        $_REQUEST = array (
            'module' => 'Leads',
            'record' => $lead->id,
            'isDuplicate' => 'false',
            'action' => 'ConvertLead',
            // Existing Contact
            'convert_create_Contacts' => 'false',
            'report_to_name' => $contact->name,
            'reports_to_id' => $contact->id,
            // Existing Account
            'convert_create_Accounts' => 'false',
            'account_name' => $account->name,
            'account_id' => $account->id,
            // Save
            'handle' => 'save',
        );

        // Call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // Refresh Lead
        $leadId = $lead->id;
        $lead = new Lead();
        $lead->retrieve($leadId);
        // Refresh Contact
        $contactId = $contact->id;
        $contact = new Contact();
        $contact->retrieve($contactId);

        // Check if contact it's linked properly
        $this->assertEquals($contact->id, $lead->contact_id, 'Contact not linked with Lead successfully.');
        // Check if account is linked with lead properly
        $this->assertEquals($account->id, $lead->account_id, 'Account not linked with Lead successfully.');
        // Check if account is linked with contact properly        
        $this->assertEquals($account->id, $contact->account_id, 'Account not linked with Contact successfully.');
        // Check Lead Status, should be converted
        $this->assertEquals('Converted', $lead->status, "Lead status should be 'Converted'.");
    }
}
