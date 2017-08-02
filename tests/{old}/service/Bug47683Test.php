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
 * This class tests that get_modified_entries returns xml with CDATA for <value> tags
 *
 */
class Bug47683Test extends SOAPTestCase
{
    public $_contact = null;
    public $_sessionId = '';

    /**
     * Create test user
     *
     */
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        $this->_setupTestContact();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown()
    {
        parent::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->_contact = null;
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestHelper::tearDown();
    }

    public function testGetModifiedEntries()
    {
        $this->_login();
        $ids = array($this->_contact->id);
        $result = $this->_soapClient->call('get_modified_entries', array('session' => $this->_sessionId, 'module_name' => 'Contacts', 'ids' => $ids, 'select_fields' => array()));
        $decoded = base64_decode($result['result']);

        $this->assertContains("<value>{$this->_contact->first_name}</value>", $decoded, "First name not found in data");
        $this->assertContains("<value>{$this->_contact->last_name}</value>", $decoded, "Last name not found in data");
    }


    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    private function _setupTestContact() {
        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->last_name .= " Пупкин-Васильев"; // test special chars
        $this->_contact->description = "<==>";
        //$this->_contact->contacts_users_id = $this->_user->id;
        $this->_contact->save();
    }

}
