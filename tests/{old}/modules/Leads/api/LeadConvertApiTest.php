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

require_once('tests/{old}/rest/RestTestBase.php');

/***
 * Used to test Lead Convert in Leads Module endpoints from LeadConvertApi.php
 */
/**
 * @group api_lead_convert
 * @group rest
 */
class LeadConvertApiTest extends RestTestBase
{
    protected $lead;

    public function setup()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        //createLead
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->lead->save();
    }
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();

        parent::tearDown();
    }

    public function testConvertLead_AllNewRecords_ConvertSuccessful(){
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $postData = array(
            "leadId" => $this->lead->id,
            "modules" => array(
                'Contacts' =>
                array(
                    'deleted' => '0',
                    'do_not_call' => '0',
                    'portal_active' => '0',
                    'preferred_language' => 'en_us',
                    'salutation' => 'Mrs.',
                    'first_name' => 'SugarLeadFirst1664617000',
                    'last_name' => 'SugarLeadLast',
                    'title' => 'd',
                    'department' => 'd',
                    'description' => '',
                    'team_id' => '',
                    'phone_home' => '',
                    'phone_mobile' => '',
                    'phone_work' => '',
                    'phone_fax' => '',
                    'primary_address_street' => '',
                    'primary_address_city' => '',
                    'primary_address_state' => '',
                    'primary_address_postalcode' => '',
                    'primary_address_country' => '',
                ),
                'Accounts' =>
                array(
                    'deleted' => '0',
                    'name' => 'd',
                    'team_id' => '',
                    'billing_address_street' => 's',
                    'billing_address_city' => 'd',
                    'billing_address_state' => 'd',
                    'billing_address_postalcode' => '',
                    'billing_address_country' => 'd',
                    'shipping_address_street' => '',
                    'shipping_address_city' => '',
                    'shipping_address_state' => '',
                    'shipping_address_postalcode' => '',
                    'shipping_address_country' => '',
                    'campaign_id' => '',
                    'phone_office' => 'd',
                    'website' => 'd',
                    'email1' => 'd',
                ),
                'Opportunities' =>
                array(
                    'deleted' => '0',
                    'forecast' => '-1',
                    'name' => 'dfdf',
                    'team_id' => '',
                    'campaign_id' => '',
                    'lead_source' => '',
                ),
            )

        );

        $response = $this->_restCall("Leads/" . $this->lead->id . '/convert', json_encode($postData), "POST");
        $lead = new Lead();
        $lead->retrieve($this->lead->id);

        $this->assertEquals(LeadConvert::STATUS_CONVERTED, $lead->status, 'Lead status field was not changed properly.');
        $this->assertEquals(1, $lead->converted, 'Lead converted field not set properly');
     }

    public function testConvertLead_RecordsExists_ConvertSuccessful(){
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $postData = array(
            "leadId" => $this->lead->id,
            "modules" => array(
                'Contacts' =>
                array(
                    'id' => $contact->id
                ),
                'Accounts' =>
                array(
                    'id' => $account->id
                ),
                'Opportunities' =>
                array(
                    'id' => $opp->id
                ),
            )
        );

        $response = $this->_restCall("Leads/" . $this->lead->id . '/convert', json_encode($postData), "POST");
        $lead = new Lead();
        $lead->retrieve($this->lead->id);

        $this->assertEquals(LeadConvert::STATUS_CONVERTED, $lead->status, 'Lead status field was not changed properly.');
        $this->assertEquals(1, $lead->converted, 'Lead converted field not set properly');
    }

    public function testConvertLead_LeadDoesNotExist_ConvertFailed(){
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $fakeLeadId = '0000330000';

        $postData = array(
            "leadId" => $this->lead->id,
            "modules" => array(
                'Contacts' =>
                array(
                    'id' => $contact->id
                ),
                'Accounts' =>
                array(
                    'id' => $account->id
                ),
                'Opportunities' =>
                array(
                    'id' => $opp->id
                ),
            )

        );

        $response = $this->_restCall("Leads/" . $fakeLeadId . '/convert', json_encode($postData), "POST");
        $this->assertEquals(500, $response['info']['http_code']);
    }
}
