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


/*
 * Tests emails subpanel query can fetch emails.
 */

class ArchivedEmailsBeanLinkTest extends Sugar_PHPUnit_Framework_TestCase
{

    // store ids of generated objects so we can delete them in tearDown
    private $email_id = '';
    private $opp_id = '';
    private $contact_id = '';
    private $account_id = '';
    private $hide_history_contacts_emails_orig = '';

    public function testRelatedEmails()
    {
        $opp = BeanFactory::getBean('Opportunities', $this->opp_id);

        $query = new SugarQuery();
        $emailsBean = BeanFactory::newBean('Emails');
        $query->select('*');
        $query->from($emailsBean);
        $query->joinSubpanel($opp, 'archived_emails', array('joinType' => 'INNER'));

        $emails = array_values($emailsBean->fetchFromQuery($query));

        $actual_email_id = $emails[0]->id;
        $this->assertEquals($this->email_id, $actual_email_id, "Email in the sub-panel is not the same as email related.");
    }

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");

        // turn on admin setting "Enable/Disable emails from related (or linked) contacts to show in Email Subpanel."
        $this->hide_history_contacts_emails_orig = $GLOBALS['sugar_config']['hide_history_contacts_emails']['Opportunities'];
        $GLOBALS['sugar_config']['hide_history_contacts_emails']['Opportunities'] = false;

        // create Opportunity, account, contact and email related (via to_addr) to contact
        $account = SugarTestAccountUtilities::createAccount();
        $account->name = "testRelateOpportunity";
        $account->save(false);
        $this->account_id = $account->id;

        $opportunity = SugarTestOpportunityUtilities::createOpportunity(null, $account);
        $this->opp_id = $opportunity->id;

        $contactValues = array(
            'first_name' => 'testRelateOpportunityFirst',
            'last_name' => 'testRelateOpportunityLast',
            'email' => 'testRelateOpportunity@testRelateOpportunity.com',
        );

        $contact = SugarTestContactUtilities::createContact('', $contactValues);
        $this->contact_id = $contact->id;
        $opportunity->load_relationship('contacts');
        $opportunity->contacts->add($contact);
        $opportunity->save();

        $override = array(
            'parent_type' => 'Contacts',
            'parent_id' => $this->contact_id,
            'to_addrs' => 'testRelateOpportunity@testRelateOpportunity.com',
        );
        $email = SugarTestEmailUtilities::createEmail(null, $override);
        $this->email_id = $email->id;
    }

    public function tearDown()
    {
        $GLOBALS['sugar_config']['hide_history_contacts_emails']['Opportunities'] = $this->hide_history_contacts_emails_orig;
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailUtilities::removeAllCreatedEmails();

        SugarTestHelper::tearDown();
    }
}
