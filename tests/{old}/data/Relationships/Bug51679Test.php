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

class Bug51679Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $account;
    private $account2;
    private $contact;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact);
        $this->account2 = SugarTestAccountUtilities::createAccount();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     *
     */
    public function testM2MRelationships()
    {
        $def = array(
            'table'=>'accounts_contacts',
            'join_table'=>'accounts_contacts',
            'name'=>'accounts_contacts',
            'lhs_module' => 'accounts',
            'rhs_module' => 'contacts'
        );
        $m2mRelationship = new M2MRelationship($def);
        $m2mRelationship->join_key_lhs = 'account_id';
        $m2mRelationship->join_key_rhs = 'contact_id';
        $result = $m2mRelationship->relationship_exists($this->account, $this->contact);

        $entry_id = $GLOBALS['db']->getOne("SELECT id FROM accounts_contacts WHERE account_id='{$this->account->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals($entry_id, $result);

        $result = $m2mRelationship->relationship_exists($this->account2, $this->contact);
        $this->assertEmpty($result);
    }
}