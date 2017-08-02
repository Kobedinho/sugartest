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

class ContactsBugFixesTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp() {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->fields = array('first_name' => 'contact', 'last_name' => 'unitTester', 'sync_contact' => '1');
        $this->prefix = 'unittest_contacts_bugfixes';
        $this->contacts = array();
    }

    public function tearDown() {
        foreach($this->fields AS $fieldName => $fieldValue) {
            unset($_POST[$fieldName]);
        }
        foreach($this->contacts AS $contact) {
            $contact->mark_deleted($contact->id);
        }

        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

	public function testBug59675ContactFormBaseRefactor() {
        $formBase = new ContactFormBase();
        foreach ($this->fields as $fieldName => $fieldValue) {
            $_POST[$this->prefix . $fieldName] = $fieldValue;
        }
        $_POST['record'] = 'asdf';
        $_REQUEST['action'] = 'save';

        $bean = $formBase->handleSave($this->prefix, false);
        $this->contacts[] = $bean;

        $this->assertTrue($bean->sync_contact == true, "Sync Contact was not set to true");

        unset($bean);
        $_POST[$this->prefix . 'sync_contact'] = '0';        

        $bean = $formBase->handleSave($this->prefix, false);
        $this->contacts[] = $bean;

        $this->assertFalse($bean->sync_contact == true, "Sync Contact was not set to false");


    }

    public function testPopulateFromApiSyncContactTrue() {
        $capih = new ContactsApiHelper(new ContactsBugFixesServiceMockup);
        $contact = BeanFactory::newBean('Contacts');
        $submittedData = array('sync_contact' => true);
        $data = $capih->populateFromApi($contact, $submittedData);
        $contact->save();
        $contact->retrieve($contact->id);
        $this->assertTrue($contact->sync_contact);
        $contact->mark_deleted($contact->id);
    }

    public function testPopulateFromApiSyncContactFalse() {
        $capih = new ContactsApiHelper(new ContactsBugFixesServiceMockup);
        $contact = BeanFactory::newBean('Contacts');
        $submittedData = array('sync_contact' => false);
        $data = $capih->populateFromApi($contact, $submittedData);
        $contact->save();
        $contact->retrieve($contact->id);
        $this->assertEmpty($contact->sync_contact);
        $contact->mark_deleted($contact->id);
    }

    public function testCRYS461Fix()
    {
        $contactApi = new ContactsApiHelper(new ContactsBugFixesServiceMockup());
        $contact = SugarTestContactUtilities::createContact();
        $contact->retrieve($contact->id);
        $this->assertEquals($contact->email1, $contact->fetched_row['email1']);

        $submittedData = array(
            'email' => array(
                array('email_address' => 'testnew@example.com', 'primary_address' => true),
                array('email_address' => 'test2@example.com', 'primary_address' => false)
            )
        );
        $contactApi->populateFromApi($contact, $submittedData);
        $this->assertNotEquals($contact->email1, $contact->fetched_row['email1']);
    }
}

class ContactsBugFixesServiceMockup extends ServiceBase {
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
?>