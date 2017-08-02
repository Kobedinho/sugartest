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
require_once 'tests/{old}/service/SOAPTestCase.php';

/**
 * @group SoapTests
 */
class PAT198Test extends SOAPTestCase
{
    /** @var Account */
    private $account;

    /** @var Contact */
    private $contact;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function setUp()
    {
        $this->account = SugarTestAccountUtilities::createAccount();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('accounts');
        $this->contact->accounts->add($this->account);
        $GLOBALS['db']->commit();

        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();

        self::$_user = $GLOBALS['current_user'];
        $this->_login();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetEntryList()
    {
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session'       => $this->_sessionId,
                'module_name'   => 'Contacts',
                'query'         => 'accounts.name=' . $GLOBALS['db']->quoted($this->account->name),
                'order_by'      => '',
                'offset'        => 0,
                'select_fields' => array('id', 'account_name'),
                'max_results'   => -1,
                'deleted'       => -1,
            )
        );

        $this->assertArrayHasKey('entry_list', $result, 'Result doesn\'t contain entry list');
        $this->assertCount(1, $result['entry_list'], 'Entry list should contain exactly one entry');
        $entry = array_shift($result['entry_list']);
        $this->assertEquals($this->contact->id, $entry['id'], 'Wrong contact is retrieved');
    }
}
