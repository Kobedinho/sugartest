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

require_once "include/export_utils.php";

class Bug43478Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Contains created prospect lists' ids
     * @var Array
     */
    protected static $_createdProspectListsIds = array();

    /**
     * Instance of ProspectList
     * @var ProspectList
     */
    protected $_prospectList;

    /**
     * Contacts array
     * @var Array
     */
    protected $_contacts = array();

    /**
     * Create contact instance (with account)
     */
    public static function createContact()
    {
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $contact->account_id = $account->id;
        $contact->primary_address_state = 'Test adress state';
        $contact->save();
        return $contact;
    }

    /**
     * Create ProspectList instance
     * @param Contact instance to attach to prospect list
     */
    public static function createProspectList($contact = null)
    {
        $prospectList = new ProspectList();
        $prospectList->name = "test";
        $prospectList->save();
        self::$_createdProspectListsIds[] = $prospectList->id;

        if ($contact instanceof Contact) {
            self::attachContactToProspectList($prospectList, $contact);
        }

        return $prospectList;
    }

    /**
     *
     * Attach Contact to prospect list
     * @param ProspectList $prospectList prospect list instance
     * @param Contact $contact contact instance
     */
    public static function attachContactToProspectList($prospectList, $contact)
    {
        $prospectList->load_relationship('contacts');
        $prospectList->contacts->add($contact->id,array());
    }

    /**
     * Set up - create prospect list with 2 contacts
     */
    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $contact = self::createContact();
        $contact->primary_address_state = 'Test adress state';
        $this->_contacts[] = $contact;
        $this->_contacts[] = self::createContact();
        $this->_prospectList = self::createProspectList($this->_contacts[0]);
        self::attachContactToProspectList($this->_prospectList, $this->_contacts[1]);
    }

    /**
     * Clear all created data
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->_clearProspects();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * Test if email exists within report
     */
    public function testEmailExistsExportList()
    {
        $content = export("ProspectLists", $this->_prospectList->id, true);
        $this->assertContains("Test adress state", $content);
    }

    private function _clearProspects()
    {
        $ids = implode("', '", self::$_createdProspectListsIds);
        $GLOBALS['db']->query('DELETE FROM prospect_list_campaigns WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists_prospects WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . $ids . '\')');
    }
}
?>
