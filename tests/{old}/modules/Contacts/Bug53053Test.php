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

class Bug53053Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public function tearDown()
    {
        if ( count($this->contactsToClean) > 0) {
            $list = "'" . implode("','", $this->contactsToClean) . "'";
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN ($list)");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN ($list)");
            }
        }

        foreach ($this->fields as $fieldName => $fieldValue) {
            unset($_POST[$this->prefix . $fieldName]);
        }
        unset($_POST['record']);
        unset($_POST[$this->prefix . 'id']);
        unset($_REQUEST['action']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testPortalPasswordSave()
    {

                //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;

        $this->contactsToClean = array();
        $this->prefix = 'unitTest';
        $this->fields = array('first_name' => 'contact', 'last_name' => 'unitTester');

        // Create seed contact
        $contact = new Contact();
        $contact->first_name = "unit 53053";
        $contact->last_name = "tester";
        $contact->save();
        $this->contact_id = $contact->id;
        $this->contactsToClean[] = $contact->id;

        $formBase = new ContactFormBase();

        //seed $_ vars
        foreach ($this->fields as $fieldName => $fieldValue) {
            $_POST[$this->prefix . $fieldName] = $fieldValue;
        }
        $_POST['record'] = 'asdf';
        $_REQUEST['action'] = 'save';

        // test case of new contact without portal password
        $bean = $formBase->handleSave($this->prefix, false);
        if ($bean->id) {
            $this->contactsToClean[] = $bean->id;
        }
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotEmpty($contact->id);
        $this->assertNull($contact->portal_password);

        // test case of new contact with portal password
        $_POST[$this->prefix . 'portal_password'] = 'asdf';

        $bean = $formBase->handleSave($this->prefix, false);
        if ($bean->id) {
            $this->contactsToClean[] = $bean->id;
        }
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotEmpty($contact->id);
        $this->assertNotNull($contact->portal_password);

        // test case set an existing records password
        $_POST[$this->prefix . 'record'] = $this->contact_id;
        $bean = $formBase->handleSave($this->prefix, false);
        $oldPass = $bean->portal_password;
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotNull($contact->portal_password);

        // test case set update existing records password
        $_POST[$this->prefix . 'portal_password'] = 'zxcv';
        $bean = $formBase->handleSave($this->prefix, false);
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotEquals($contact->portal_password, $oldPass);
        $oldPass = $contact->portal_password;

        // test case don't update password
        $_POST[$this->prefix . 'portal_password'] = 'value_setvalue_setvalue_set';
        // Set the record into the request so we continue to work on the right bean
        $_REQUEST[$this->prefix . 'record'] = $bean->id;
        $bean = $formBase->handleSave($this->prefix, false);
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertEquals($contact->portal_password, $oldPass);

        // test clear password
        $_POST[$this->prefix . 'portal_password'] = '';
        $bean = $formBase->handleSave($this->prefix, false);
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertEmpty($contact->portal_password);


    }
}
