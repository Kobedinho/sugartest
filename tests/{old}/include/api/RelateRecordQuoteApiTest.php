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


/**
 * Bug #57888
 * REST API: Create related quote must populate billing/shipping contact and account
 *
 * @ticket 57888
 */
class RelateRecordQuoteApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_api;
    private $_contact;
    private $_account;
    private $_args;
    private $_quoteName;
    private $_apiClass;
    private $_address_fields = array('address_street', 'address_city', 'address_state', 'address_street', 'address_street');

    public function setUp(){
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_api = new RestService();
        $this->_api->user = $GLOBALS['current_user'];

        $this->_quoteName = 'RelateRecordQuoteApiTestQuote'.time();
        $this->_args = array(
            "module" => "Contacts",
            "record" => $this->_contact->id,
            "link_name" => "quotes",
            "name" => $this->_quoteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
            "date_quote_expected_closed" => TimeDate::getInstance()->getNow()->asDbDate(),
        );

        $this->_apiClass = new RelateRecordApi();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();

        unset( $this->_api,  $this->_contact, $this->_account, $this->_args, $this->_quoteName, $this->_apiClass);
    }

    private function fillAddressArgs()
    {
        $address_types = array('shipping', 'billing');
        $time = time();

        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $this->_args[$_type.'_'.$_field] = $_type.'_'.$_field.$time;
            }
        }
    }

    private function fillAddressForContact($address = 'primary')
    {
        $address = in_array($address, array('primary', 'alt')) ? $address : 'primary';
        $time = time();
        foreach ( $this->_address_fields as $_field )
        {
            $_field = $address.'_'.$_field;
            $this->_contact->$_field = $_field.$time;
        }
        $this->_contact->save(false);
    }

    private function fillAddressForAccount($address = 'billing')
    {
        $address = in_array($address, array('billing', 'shipping')) ? $address : 'billing';
        $time = time();
        foreach ($this->_address_fields as $_field)
        {
            $_field = $address.'_'.$_field;
            $this->_account->$_field = $_field.$time;
        }
        $this->_account->save(false);
    }

    private function createAccountForContact()
    {
        $this->_account = SugarTestAccountUtilities::createAccount();
        $this->_contact->account_id = $this->_account->id;
        $this->_contact->save(false);
    }

    private function assertRelatedItemExists($result)
    {
        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['related_record']['id']);
        $this->assertEquals($this->_quoteName, $result['related_record']['name']);

        $quote = new Quote();
        $quote->retrieve($result['related_record']['id']);
        SugarTestQuoteUtilities::setCreatedQuote(array($result['related_record']['id']));

        $this->_contact->load_relationship("quotes");
        $relatedIds = $this->_contact->quotes->get();
        $this->assertNotEmpty($relatedIds);
        $this->assertEquals($quote->id, $relatedIds[0]);

        $this->assertArrayHasKey('shipping_contact_name', $result['related_record']);
        $this->assertEquals($this->_contact->name, $result['related_record']['shipping_contact_name']);

        $this->assertArrayHasKey('shipping_contact_id', $result['related_record']);
        $this->assertEquals($this->_contact->id, $result['related_record']['shipping_contact_id']);
    }
    /**
     * test case when there are NOT request params and contact has NOT primary and alt address
     */
    public function testCreateRelatedQuoteToContact()
    {

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // contact has not address and there are not request data to populate - all address fields should be empty
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals('', $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has NOT primary and alt address
     */
    public function testCreateRelatedQuoteToContactWithParams()
    {
        $this->fillAddressArgs();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // contact has not address but there are request data to populate - all address fields should be populated form request
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are NOT request params and contact has primary address
     */
    public function testCreateRelatedQuoteToContactWithAddress()
    {
        $this->fillAddressForContact();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);


        // billing address is populated when contact has account only
        // shipping address is populated from primary address of contact
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field_to_check = 'primary_'.$_field;
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_contact->$_field_to_check, $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has primary address
     */
    public function testCreateRelatedQuoteToContactWithAddressAndParams()
    {
        $this->fillAddressArgs();
        $this->fillAddressForContact();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // billing address is populated when contact has account only
        // shipping address is populated from request
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are NOT request params and contact has alt address (primary address is empty)
     */
    public function testCreateRelatedQuoteToContactWithAltAddress()
    {
        $this->fillAddressForContact('alt');

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // billing address is populated when contact has account only
        // shipping address is populated from alt address of contact
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field_to_check = 'alt_'.$_field;
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_contact->$_field_to_check, $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has alt address (primary address is empty)
     */
    public function testCreateRelatedQuoteToContactWithAltAddressAndParams()
    {
        $this->fillAddressArgs();
        $this->fillAddressForContact('alt');

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // billing address is populated when contact has account only
        // shipping address is populated from request
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are NOT request params and contact has related account
     */
    public function testCreateRelatedQuoteToContactWithAccount()
    {
        $this->createAccountForContact();
        $this->fillAddressForAccount();
        $this->fillAddressForContact();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // check is account related
        $this->assertArrayHasKey('account_name', $result['related_record']);
        $this->assertEquals($this->_account->name, $result['related_record']['account_name']);
        $this->assertArrayHasKey('account_id', $result['related_record']);
        $this->assertEquals($this->_account->id, $result['related_record']['account_id']);

        // contact has account and billing contact should be populated
        $this->assertArrayHasKey('billing_contact_name', $result['related_record']);
        $this->assertEquals($this->_contact->name, $result['related_record']['billing_contact_name']);
        $this->assertArrayHasKey('billing_contact_id', $result['related_record']);
        $this->assertEquals($this->_contact->id, $result['related_record']['billing_contact_id']);
        
        // contact has account and billing address should be populated
        // shipping and billing address are populated from primary address of contact
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            $bean = ($_type === 'billing') ? $this->_account : $this->_contact;
            $field_type = ($_type === 'billing') ? 'billing' : 'primary';
            foreach ( $this->_address_fields as $_field )
            {
                $_field_to_check = $field_type . '_'.$_field;
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($bean->$_field_to_check, $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has related account
     */
    public function testCreateRelatedQuoteToContactWithAccountAndParams()
    {
        $this->createAccountForContact();
        $this->fillAddressArgs();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // check is account related
        $this->assertArrayHasKey('account_name', $result['related_record']);
        $this->assertEquals($this->_account->name, $result['related_record']['account_name']);
        $this->assertArrayHasKey('account_id', $result['related_record']);
        $this->assertEquals($this->_account->id, $result['related_record']['account_id']);

        // contact has account and billing contact should be populated
        $this->assertArrayHasKey('billing_contact_name', $result['related_record']);
        $this->assertEquals($this->_contact->name, $result['related_record']['billing_contact_name']);
        $this->assertArrayHasKey('billing_contact_id', $result['related_record']);
        $this->assertEquals($this->_contact->id, $result['related_record']['billing_contact_id']);

        // contact has account and billing address should be populated
        // shipping and billing address are populated from request
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }

    public function testCreateRelatedQuoteToAccount()
    {
        $this->_account = SugarTestAccountUtilities::createAccount();
        $this->fillAddressForAccount();
        $this->fillAddressForAccount('shipping');
        $this->_args = array(
            "module" => "Accounts",
            "record" => $this->_account->id,
            "link_name" => "quotes",
            "name" => $this->_quoteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
            "date_quote_expected_closed" => TimeDate::getInstance()->getNow()->asDbDate(),
        );

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);

        // contact has account and billing address should be populated
        // shipping and billing address are populated from primary address of contact
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field_to_check =  $_type.'_'.$_field;
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_account->$_field_to_check, $result['related_record'][$_field]);
            }
        }
    }
}
