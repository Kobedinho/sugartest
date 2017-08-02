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


class CRYS528Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testLegacyEmailFieldUpdate()
    {
        $service = SugarTestRestUtilities::getRestServiceMock();

        $contact = SugarTestContactUtilities::createContact();
        $contact->retrieve();

        $api = new ModuleApi();

        $params = array('module' => 'Contacts', 'record' => $contact->id, 'email1' => '');
        $result = $api->updateRecord($service, $params);
        $this->assertEquals($params['email1'], $result['email1']);
        $this->assertEmpty($result['email']);
        $contact->retrieve();
        $this->assertEquals($params['email1'], $contact->email1);

        $params = array('module' => 'Contacts', 'record' => $contact->id, 'email1' => 'test@email2.com');
        $result = $api->updateRecord($service, $params);
        $this->assertEquals($params['email1'], $result['email1']);
        $contact->retrieve();
        $this->assertEquals($params['email1'], $contact->email1);
    }
}
