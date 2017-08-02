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


class QuotesApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var QuotesApiHelper
     */
    protected $helper;

    private $_address_fields = array(
        'address_street',
        'address_city',
        'address_state',
        'address_street',
        'address_street'
    );

    public function setUp()
    {
        parent::setUp();

        $mock_service = new QuotesServiceMock();
        $mock_service->user = SugarTestHelper::setUp('current_user');

        $this->helper = $this->getMockBuilder('QuotesApiHelper')->setMethods(array('execute'))->setConstructorArgs(array($mock_service))->getMock();
    }

    public function tearDown()
    {
        unset($this->helper);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testPopulateFromApiSettingBillingAddressCorrectly()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->fillAddressForAccount($account);

        /* @var $bean Quote */
        $bean = $this->getMockBuilder('Quote')
            ->setMethods(array('save'))
            ->getMock();

        $data = array(
            "name" => 'test_quote' . time(),
            "assigned_user_id" => $GLOBALS['current_user']->id,
            "date_quote_expected_closed" => TimeDate::getInstance()->getNow()->asDbDate(),
            'billing_account_id' => $account->id
        );

        $this->helper->populateFromApi($bean, $data);

        foreach ($this->_address_fields as $field) {
            $_field = 'billing_' . $field;
            $this->assertEquals($account->$_field, $bean->$_field);
        }
    }

    public function testPopulateFromApiWithSettingBillingAccountIdWillFillingShippingAccountInfo()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->fillAddressForAccount($account);

        /* @var $bean Quote */
        $bean = $this->getMockBuilder('Quote')
            ->setMethods(array('save'))
            ->getMock();

        $data = array(
            "name" => 'test_quote' . time(),
            "assigned_user_id" => $GLOBALS['current_user']->id,
            "date_quote_expected_closed" => TimeDate::getInstance()->getNow()->asDbDate(),
            'billing_account_id' => $account->id
        );

        $this->helper->populateFromApi($bean, $data);

        $this->assertEquals($account->id, $bean->shipping_account_id);

        foreach ($this->_address_fields as $field) {
            $_bean_field = 'billing_' . $field;
            $_field = 'shipping_' . $field;
            $this->assertEquals($account->$_bean_field, $bean->$_field);
        }
    }

    public function testPopulateFromApiWillSetCorrectShippingInfo()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->fillAddressForAccount($account);
        $this->fillAddressForAccount($account, 'shipping');

        /* @var $bean Quote */
        $bean = $this->getMockBuilder('Quote')
            ->setMethods(array('save'))
            ->getMock();

        $data = array(
            "name" => 'test_quote' . time(),
            "assigned_user_id" => $GLOBALS['current_user']->id,
            "date_quote_expected_closed" => TimeDate::getInstance()->getNow()->asDbDate(),
            'billing_account_id' => $account->id,
        );

        $this->helper->populateFromApi($bean, $data);

        $this->assertEquals($account->id, $bean->shipping_account_id);

        foreach ($this->_address_fields as $field) {
            $_field = 'shipping_' . $field;
            $this->assertEquals($account->$_field, $bean->$_field);
        }
    }

    public function testPopulateFromApiSetBillingFromAccountAndShippingFromContact()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->fillAddressForAccount($account);
        $this->fillAddressForAccount($account, 'shipping');

        $contact = SugarTestContactUtilities::createContact();
        $this->fillAddressForContact($contact);
        $this->fillAddressForContact($contact, 'alt');

        /* @var $bean Quote */
        $bean = $this->getMockBuilder('Quote')
            ->setMethods(array('save'))
            ->getMock();

        $data = array(
            "name" => 'test_quote' . time(),
            "assigned_user_id" => $GLOBALS['current_user']->id,
            "date_quote_expected_closed" => TimeDate::getInstance()->getNow()->asDbDate(),
            'billing_account_id' => $account->id,
            'billing_contact_id' => $contact->id,
            'shipping_account_id' => $account->id,
            'shipping_contact_id' => $contact->id,
        );

        $this->helper->populateFromApi($bean, $data);

        $this->assertEquals($account->id, $bean->shipping_account_id);

        foreach ($this->_address_fields as $field) {
            $_field = 'billing_' . $field;
            $this->assertEquals($account->$_field, $bean->$_field, 'Billing ' . $field . ' does not match');
        }

        foreach ($this->_address_fields as $field) {
            $_bean_field = 'primary_' . $field;
            $_field = 'shipping_' . $field;
            $this->assertEquals($contact->$_bean_field, $bean->$_field, 'Shipping ' . $field . ' does not match');
        }
    }

    private function fillAddressForAccount($account, $address = 'billing')
    {
        $address = in_array($address, array('billing', 'shipping')) ? $address : 'billing';
        $time = time();
        foreach ($this->_address_fields as $_field) {
            $_field = $address . '_' . $_field;
            $account->$_field = $_field . $time;
        }
        $account->save(false);
    }

    private function fillAddressForContact($contact, $address = 'primary')
    {
        $address = in_array($address, array('primary', 'alt')) ? $address : 'primary';
        $time = time();
        foreach ($this->_address_fields as $_field) {
            $_field = $address . '_' . $_field;
            $contact->$_field = $_field . $time;
        }
        $contact->save(false);
    }
}

class QuotesServiceMock extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(Exception $exception)
    {
    }
}
