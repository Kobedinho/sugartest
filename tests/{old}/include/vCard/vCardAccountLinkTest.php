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


class vCardAccountLinkTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $contactId;
    private $leadId;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $account = SugarTestAccountUtilities::createAccount();
        $account->name = "SDizzle Inc";
        $account->save();
    }
    
    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contactId}'");
        $GLOBALS['db']->query("DELETE FROM leads WHERE id  = '{$this->leadId}'");
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if account is linked with bean when importing from vCard
     */
    public function testImportedVcardAccountLink()
    {
        $filename  = dirname(__FILE__) . "/vcf/SimpleVCard.vcf";
        
        $vcard = new vCard();
        $this->contactId = $vcard->importVCard($filename, 'Contacts');
        $contactRecord = BeanFactory::getBean('Contacts', $this->contactId);
        
        $this->assertFalse(empty($contactRecord->account_id), "Contact should have an account record associated");

        $vcard = new vCard();
        $this->leadId = $vcard->importVCard($filename, 'Leads');
        $leadRecord = BeanFactory::getBean('Leads', $this->leadId);

        $this->assertTrue(empty($leadRecord->account_id), "Lead should not have an account record associated");
    }
}
