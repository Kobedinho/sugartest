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
require_once 'tests/{old}/SugarTestRestUtilities.php';

/**
 * @group ApiTests
 */
class PAT189Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var AccountsRelateApi */
    private $api;
    private $serviceMock;

    /** @var Account */
    private $account1;

    /** @var Account */
    private $account2;

    /** @var Contact */
    private $contact;

    /** @var Call */
    private $call;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->api = new AccountsRelateApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        $this->account1 = SugarTestAccountUtilities::createAccount();
        $this->account2 = SugarTestAccountUtilities::createAccount();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('accounts');
        $this->contact->accounts->add($this->account1);

        $this->call = SugarTestCallUtilities::createCall();
        $this->call->load_relationship('contacts');
        $this->call->contacts->add($this->contact);
    }

    protected function tearDown()
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testRelatedCallIsSelected()
    {
        $calls = $this->getCalls($this->account1);
        $this->assertContains($this->call->id, $calls);
    }

    public function testUnrelatedCallIsNotSelected()
    {
        $calls = $this->getCalls($this->account2);
        $this->assertNotContains($this->call->id, $calls);
    }

    private function getCalls(Account $account)
    {
        $result = $this->api->filterRelated(
            $this->serviceMock,
            array(
                'module' => 'Accounts',
                'record' => $account->id,
                'link_name' => 'calls',
                'include_child_items' => true,
            )
        );

        $this->assertArrayHasKey('records', $result, 'Filter result doesn\'t have "records" key');
        $this->assertInternalType('array', $result['records'], 'Filter result "records" is not an array');

        $calls = array();
        foreach ($result['records'] as $record) {
            $this->assertArrayHasKey('id', $record, 'Record doesn\'t have "id" key');
            $calls[] = $record['id'];
        }

        return $calls;
    }
}
