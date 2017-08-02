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
 * RS-152
 * Prepare Cases Api
 */
class RS152Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var CasesApi */
    protected $api = null;

    /** @var Account */
    protected $account = null;

    /** @var Contact */
    protected $contact = null;

    /** @var aCase */
    protected $case = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();

        $this->api = new CasesApi();

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->load_relationship('contacts');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->account_id = $this->account->id;
        $this->contact->assigned_user_id = 1;
        $this->contact->team_id = 1;
        $this->contact->team_set_id = 1;
        $this->contact->save();
        $this->account->contacts->add($this->contact);


        $_SESSION['type'] = 'support_portal';
        $_SESSION['contact_id'] = $this->contact->id;
    }

    public function tearDown()
    {
        unset($_SESSION['type'], $_SESSION['contact_id']);
        if ($this->case instanceof aCase) {
            $this->case->mark_deleted($this->case->id);
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of createRecord method
     */
    public function testCreateRecord()
    {
        $data = $this->api->createRecord($this->service, array(
                'module' => 'Cases',
                'name' => 'Case ' . __CLASS__,
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'team_id' => 2,
                'team_set_id' => 2,
            ));
        $this->assertArrayHasKey('id', $data);

        $this->case = BeanFactory::getBean('Cases', $data['id']);
        $this->assertEquals($this->contact->assigned_user_id, $this->case->assigned_user_id);
        $this->assertEquals($this->contact->team_id, $this->case->team_id);
        $this->assertEquals($this->contact->team_set_id, $this->case->team_set_id);

        $this->case->load_relationship('contacts');
        $this->case->load_relationship('accounts');
        $contacts = $this->case->contacts->getBeans();
        $this->assertArrayHasKey($this->contact->id, $contacts);
        $accounts = $this->case->accounts->getBeans();
        $this->assertArrayHasKey($this->account->id, $accounts);
    }
}
