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
 * Used to test Leads Module endpoint from LeadsApi.php
 *
 * @group leads
 */
class LeadsApiTest extends RestTestBase
{
    /**
     * @group leadsapi
     */
    public function testConvertProspect()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        global $db;

        $prospectId = $this->createProspect();
        $campaignId = $this->createCampaign();

        $url = 'Leads';
        $postBody = 'last_name=TestLeadFromConvertedProspect&prospect_id='.$prospectId.'&campaign_id='.$campaignId;
        $return = $this->_restCall($url, $postBody, 'POST');

        //verify lead was created
        $leadId = $return['reply']['id'];
        $this->assertNotEmpty($leadId, 'Lead should be created');

        //verify lead link was created
        $prospect = new Prospect();
        $prospect->retrieve($prospectId);
        $this->assertEquals($leadId, $prospect->lead_id, 'Lead id should be set on the prospect');

        //verify campaign log was created
        $campaign = new Campaign();
        $campaign->retrieve($campaignId);
        $campaignLogQuery = $campaign->track_log_leads();
        $result = $db->query($campaignLogQuery);
        $row = $db->fetchByAssoc($result);
        $this->assertEquals('lead', $row['activity_type'], 'Campaign log activity type should be lead');
        $this->assertEquals($prospectId, $row['related_id'], 'Campaign log related_id should be the prospect id');
        $this->assertEquals($leadId, $row['target_id'], 'Campaign log target_id should be the lead id');
    }

    /**
     * @group leadsapi
     */
    public function testEmailToLead()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $emailId = $this->createEmail();

        $url = 'Leads';
        $postBody = 'last_name=TestLeadFromEmail&inbound_email_id='.$emailId;
        $return = $this->_restCall($url, $postBody, 'POST');

        //verify lead was created
        $leadId = $return['reply']['id'];
        $this->assertNotEmpty($leadId, 'Lead should be created');

        //verify email updated correctly with relationship
        $email = new Email();
        $email->retrieve($emailId);
        $this->assertEquals('Leads', $email->parent_type, 'Parent type should be Leads');
        $this->assertEquals($leadId, $email->parent_id, 'Lead relationship should be set');
        $this->assertEquals('read', $email->status, 'Email status should be read');
    }

    // UTILITY CLASSES

    protected function createProspect()
    {
        $prospect = new Prospect();
        $prospect->last_name = 'TestProspect';
        $prospect->save();
        return $prospect->id;
    }

    protected function createCampaign()
    {
        $campaign = new Campaign();
        $campaign->name = 'TestCampaign';
        $campaign->save();
        return $campaign->id;
    }

    protected function createEmail()
    {
        $email = new Email();
        $email->name = 'TestEmail';
        $email->status = 'unread';
        $email->save();
        return $email->id;
    }

}
