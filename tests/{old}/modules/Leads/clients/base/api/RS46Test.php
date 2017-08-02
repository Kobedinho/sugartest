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
 * RS-46
 * Prepare Leads Api
 */
class RS46Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var LeadsApi */
    protected $api = null;

    /** @var array */
    protected $accounts = array();

    /** @var array */
    protected $leads = array();

    /** @var array */
    protected $prospects = array();

    /** @var array */
    protected $campaigns = array();

    /** @var array */
    protected $campaignLogs = array();

    /** @var array */
    protected $emails = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new LeadsApi();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query('DELETE FROM accounts WHERE id IN (\'' . implode("', '", $this->accounts) . '\')');
        $GLOBALS['db']->query('DELETE FROM leads WHERE id IN (\'' . implode("', '", $this->leads) . '\')');
        $GLOBALS['db']->query('DELETE FROM prospects WHERE id IN (\'' . implode("', '", $this->prospects) . '\')');
        $GLOBALS['db']->query('DELETE FROM campaigns WHERE id IN (\'' . implode("', '", $this->campaigns) . '\')');
        $GLOBALS['db']->query('DELETE FROM campaign_log WHERE id IN (\'' . implode("', '", $this->campaignLogs) . '\')');
        $GLOBALS['db']->query('DELETE FROM emails WHERE id IN (\'' . implode("', '", $this->emails) . '\')');
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts successful creation of Lead
     */
    public function testCreateRecord()
    {
        $actual = $this->api->createRecord($this->service, array(
                'module' => 'Leads',
                'name' => 'Lead ' . __CLASS__,
            ));
        $this->assertArrayHasKey('id', $actual);
        $this->assertNotEmpty($actual['id']);
        $this->leads[] = $actual['id'];
    }

    /**
     * Test asserts that Lead is linked with Prospect and goes to CampaignLog if Campaign is present
     */
    public function testConvertProspect()
    {
        $prospect = new Prospect();
        $prospect->name = 'Prospect ' . __CLASS__;
        $prospect->save();
        $this->prospects[] = $prospect->id;
        $this->assertEmpty($prospect->lead_id);

        $campaign = new Campaign();
        $campaign->name = 'Campaign ' . __CLASS__;
        $campaign->save();
        $this->campaigns[] = $campaign->id;

        $actual = $this->api->createRecord($this->service, array(
                'module' => 'Leads',
                'name' => 'Test ' . __CLASS__,
                'relate_to' => 'Prospects',
                'relate_id' => $prospect->id,
                'campaign_id' => $campaign->id,
            ));
        $this->assertArrayHasKey('id', $actual);
        $this->assertNotEmpty($actual['id']);
        $this->leads[] = $actual['id'];

        $prospect->retrieve($prospect->id);
        $this->assertEquals($actual['id'], $prospect->lead_id);

        $campaignLog = new CampaignLog();
        $log = $campaignLog->create_new_list_query(
            '',
            'related_id=' . $GLOBALS['db']->quoted($prospect->id)
            . ' AND related_type=' . $GLOBALS['db']->quoted($prospect->module_dir)
            . ' AND target_id=' . $GLOBALS['db']->quoted($actual['id'])
            . ' AND target_type=' . $GLOBALS['db']->quoted('Leads')
        );
        $log = $GLOBALS['db']->fetchOne($log);
        $this->assertNotEmpty($log);
        $this->campaignLogs[] = $log['id'];
    }

    /**
     * Test asserts that Lead is linked with Email if Email is present
     */
    public function testLinkLeadToEmail()
    {
        $email = new Email();
        $email->name = 'Email ' . __CLASS__;
        $email->save();
        $this->emails[] = $email->id;
        $this->assertEmpty($email->parent_type);
        $this->assertEmpty($email->parent_id);

        $actual = $this->api->createRecord($this->service, array(
                'module' => 'Leads',
                'name' => 'Test ' . __CLASS__,
                'inbound_email_id' => $email->id,
            ));
        $this->assertArrayHasKey('id', $actual);
        $this->assertNotEmpty($actual['id']);
        $this->leads[] = $actual['id'];

        $email->retrieve($email->id);
        $this->assertEquals('Leads', $email->parent_type);
        $this->assertEquals($actual['id'], $email->parent_id);
    }

    /**
     * Test asserts that we can get Account of Lead
     */
    public function testGetAccountBean()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->accounts[] = $account->id;
        $lead = SugarTestLeadUtilities::createLead();
        $this->leads[] = $lead->id;
        $lead->load_relationship('accounts');
        $lead->accounts->add($account);

        $api = new ReflectionObject($this->api);

        $getAccountBean = $api->getMethod('getAccountBean');
        $getAccountBean->setAccessible(true);
        $actual = $getAccountBean->invokeArgs($this->api, array($this->service, array(), $lead));

        $this->assertNotEmpty($actual);
        $this->assertEquals($account->id, $actual->id);
    }

    /**
     * Test asserts that we can get Lead of Account
     */
    public function testGetAccountRelationship()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->accounts[] = $account->id;
        $lead = SugarTestLeadUtilities::createLead();
        $this->leads[] = $lead->id;
        $lead->load_relationship('accounts');
        $lead->accounts->add($account);


        $api = new ReflectionObject($this->api);

        $getAccountRelationship = $api->getMethod('getAccountRelationship');
        $getAccountRelationship->setAccessible(true);
        $actual = $getAccountRelationship->invokeArgs($this->api, array($this->service, array(), $account, 'leads'));

        $this->assertNotEmpty($actual);
        $this->assertEquals($lead->id, $actual[0]['id']);
    }
}
